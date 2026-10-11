<?php

require_once "includes/auth.php";
requireRole(["admin", "manager", "staff"]);

require_once "config/database.php";

$message = "";
$message_type = "";


/*CHECK TRANSACTION ID*/

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {

    header("Location: stock.php");
    exit();

}

$id = intval($_GET["id"]);


/* UPDATE STOCK TRANSACTION*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $new_ingredient_id =
        intval($_POST["ingredient_id"]);

    $new_type =
        $_POST["transaction_type"];

    $new_quantity =
        floatval($_POST["quantity"]);

    $new_remarks =
        trim($_POST["remarks"]);


    /*
     * Validate
     */

    if (
        $new_ingredient_id <= 0 ||
        $new_quantity <= 0 ||
        (
            $new_type !== "IN" &&
            $new_type !== "OUT"
        )
    ) {

        $message =
            "Please enter valid stock information.";

        $message_type =
            "error";

    } else {

        $conn->begin_transaction();

        try {

            /*
             * Get the original transaction
             */
            $old_sql = "
                SELECT
                    ingredient_id,
                    transaction_type,
                    quantity

                FROM stock_transactions

                WHERE id = ?

                FOR UPDATE
            ";

            $old_stmt =
                $conn->prepare($old_sql);

            $old_stmt->bind_param(
                "i",
                $id
            );

            $old_stmt->execute();

            $old_result =
                $old_stmt->get_result();


            if (
                $old_result->num_rows === 0
            ) {

                throw new Exception(
                    "Stock transaction not found."
                );
            }


            $old_transaction =
                $old_result->fetch_assoc();


            $old_ingredient_id =
                intval(
                    $old_transaction[
                        "ingredient_id"
                    ]
                );

            $old_type =
                $old_transaction[
                    "transaction_type"
                ];

            $old_quantity =
                floatval(
                    $old_transaction[
                        "quantity"
                    ]
                );


            /*
             * Get all affected ingredient IDs.
             *
             * If ingredient was changed, both the old
             * and new ingredient need to be updated.
             */

            $ingredient_ids = array_unique([
                $old_ingredient_id,
                $new_ingredient_id
            ]);


            /*
             * Lock ingredient rows
             */

            $ingredient_quantities = [];


            foreach ($ingredient_ids as $ingredient_id) {

                $ingredient_sql = "
                    SELECT quantity

                    FROM ingredients

                    WHERE id = ?

                    FOR UPDATE
                ";

                $ingredient_stmt =
                    $conn->prepare(
                        $ingredient_sql
                    );

                $ingredient_stmt->bind_param(
                    "i",
                    $ingredient_id
                );

                $ingredient_stmt->execute();

                $ingredient_result =
                    $ingredient_stmt->get_result();


                if (
                    $ingredient_result->num_rows === 0
                ) {

                    throw new Exception(
                        "Ingredient not found."
                    );
                }


                $ingredient_row =
                    $ingredient_result->fetch_assoc();


                $ingredient_quantities[
                    $ingredient_id
                ] =
                    floatval(
                        $ingredient_row[
                            "quantity"
                        ]
                    );
            }


            /*
             * Remove the effect of the OLD transaction
             */

            $old_current =
                $ingredient_quantities[
                    $old_ingredient_id
                ];


            if ($old_type === "IN") {

                /*
                 * Old IN added stock,
                 * so remove it.
                 */

                $after_old =
                    $old_current -
                    $old_quantity;

            } else {

                /*
                 * Old OUT removed stock,
                 * so add it back.
                 */

                $after_old =
                    $old_current +
                    $old_quantity;
            }


            if ($after_old < 0) {

                throw new Exception(
                    "This transaction cannot be edited because the current stock has already been reduced below the original transaction amount."
                );
            }


            if (
                $old_ingredient_id ===
                $new_ingredient_id
            ) {

                $new_base_quantity =
                    $after_old;

            } else {

                $new_base_quantity =
                    $ingredient_quantities[
                        $new_ingredient_id
                    ];
            }


            /*
             * Apply the NEW transaction
             */

            if ($new_type === "IN") {

                $new_final_quantity =
                    $new_base_quantity +
                    $new_quantity;

            } else {

                if (
                    $new_quantity >
                    $new_base_quantity
                ) {

                    throw new Exception(
                        "Not enough stock available for the new Stock Out transaction. Available stock: "
                        . number_format(
                            $new_base_quantity,
                            2
                        )
                    );
                }


                $new_final_quantity =
                    $new_base_quantity -
                    $new_quantity;
            }


            /*
             * Update OLD ingredient
             */

            $update_old_sql = "
                UPDATE ingredients

                SET quantity = ?

                WHERE id = ?
            ";

            $update_old_stmt =
                $conn->prepare(
                    $update_old_sql
                );


            if (
                $old_ingredient_id ===
                $new_ingredient_id
            ) {

                $update_old_stmt->bind_param(
                    "di",
                    $new_final_quantity,
                    $old_ingredient_id
                );

                $update_old_stmt->execute();

            } else {

                /*
                 * Old ingredient gets its quantity
                 * after removing the old transaction.
                 */

                $update_old_stmt->bind_param(
                    "di",
                    $after_old,
                    $old_ingredient_id
                );

                $update_old_stmt->execute();


                /*
                 * New ingredient gets new transaction.
                 */

                $update_new_sql = "
                    UPDATE ingredients

                    SET quantity = ?

                    WHERE id = ?
                ";

                $update_new_stmt =
                    $conn->prepare(
                        $update_new_sql
                    );

                $update_new_stmt->bind_param(
                    "di",
                    $new_final_quantity,
                    $new_ingredient_id
                );

                $update_new_stmt->execute();
            }


            /*
             * Update transaction record
             */

            $update_transaction_sql = "
                UPDATE stock_transactions

                SET
                    ingredient_id = ?,
                    transaction_type = ?,
                    quantity = ?,
                    remarks = ?

                WHERE id = ?
            ";


            $update_transaction_stmt =
                $conn->prepare(
                    $update_transaction_sql
                );


            $update_transaction_stmt->bind_param(
                "isdsi",
                $new_ingredient_id,
                $new_type,
                $new_quantity,
                $new_remarks,
                $id
            );


            if (
                !$update_transaction_stmt->execute()
            ) {

                throw new Exception(
                    "Failed to update stock transaction."
                );
            }


            /*
             * Commit
             */

            $conn->commit();


            header(
                "Location: stock.php?updated=1"
            );

            exit();


        } catch (Exception $e) {

            $conn->rollback();

            $message =
                $e->getMessage();

            $message_type =
                "error";
        }
    }
}


/* GET CURRENT TRANSACTION */

$sql = "
    SELECT
        stock_transactions.id,
        stock_transactions.ingredient_id,
        stock_transactions.transaction_type,
        stock_transactions.quantity,
        stock_transactions.remarks,
        stock_transactions.transaction_date,

        ingredients.ingredient_name,
        ingredients.unit

    FROM stock_transactions

    INNER JOIN ingredients
        ON stock_transactions.ingredient_id =
           ingredients.id

    WHERE stock_transactions.id = ?
";


$stmt =
    $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result =
    $stmt->get_result();


if ($result->num_rows === 0) {

    header("Location: stock.php");
    exit();

}


$transaction =
    $result->fetch_assoc();


/* GET INGREDIENTS */

$ingredients = $conn->query("
    SELECT
        id,
        ingredient_name,
        unit,
        quantity

    FROM ingredients

    ORDER BY ingredient_name ASC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Edit Stock Transaction - StockIt
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

    <script
        src="js/script.js"
        defer
    ></script>

</head>


<body class="app-body">


<?php include "includes/sidebar.php"; ?>


<main class="main-content">


    <!--TOP BAR -->

    <div class="topbar">

        <div>

            <p class="page-label">
                INVENTORY
            </p>

            <h1>
                Edit Stock Transaction
            </h1>

        </div>


        <div class="topbar-user">

            <div class="topbar-avatar">

                <?php
                echo strtoupper(
                    substr(
                        $_SESSION["name"],
                        0,
                        1
                    )
                );
                ?>

            </div>


            <div>

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $_SESSION["name"]
                    );
                    ?>

                </strong>


                <span>

                    <?php
                    echo ucfirst(
                        htmlspecialchars(
                            $_SESSION["role"]
                        )
                    );
                    ?>

                </span>

            </div>

        </div>

    </div>


    <!-- PAGE HEADER-->

    <div class="page-header">


        <div>

            <p class="page-label">
                INVENTORY
            </p>

            <h1>
                Edit Stock Transaction
            </h1>

        </div>


        <a
            href="stock.php"
            class="back-dashboard-btn"
        >
            ← Back to Stock Management
        </a>


    </div>


    <!-- DESCRIPTION -->

    <div class="section-heading">

        <p>
            STOCK CONTROL
        </p>

        <h2>
            Update Stock Transaction
        </h2>

    </div>


    <!-- ERROR MESSAGE -->

    <?php if (!empty($message)): ?>

        <div
            class="<?php
                echo $message_type === "success"
                    ? "success-message"
                    : "error-message";
            ?>"
        >

            <?php
            echo htmlspecialchars(
                $message
            );
            ?>

        </div>

    <?php endif; ?>


    <!-- EDIT FORM -->

    <div class="modern-card">


        <div class="card-heading">

            <div>

                <p class="card-label">
                    STOCK
                </p>

                <h2>
                    Transaction Details
                </h2>

            </div>

        </div>


        <form method="POST">


            <div class="form-row">


                <!-- INGREDIENT -->

                <div class="form-group">

                    <label>
                        Ingredient
                    </label>


                    <select
                        name="ingredient_id"
                        required
                    >


                        <option value="">
                            Select Ingredient
                        </option>


                        <?php while (
                            $ingredient =
                            $ingredients->fetch_assoc()
                        ): ?>


                            <option
                                value="<?php
                                    echo $ingredient["id"];
                                ?>"

                                <?php
                                echo (
                                    $ingredient["id"]
                                    ==
                                    $transaction[
                                        "ingredient_id"
                                    ]
                                )
                                    ? "selected"
                                    : "";
                                ?>
                            >

                                <?php
                                echo htmlspecialchars(
                                    $ingredient[
                                        "ingredient_name"
                                    ]
                                );
                                ?>

                                -

                                <?php
                                echo number_format(
                                    $ingredient[
                                        "quantity"
                                    ],
                                    2
                                );
                                ?>

                                <?php
                                echo htmlspecialchars(
                                    $ingredient["unit"]
                                );
                                ?>

                            </option>


                        <?php endwhile; ?>


                    </select>

                </div>


                <!-- TRANSACTION TYPE -->

                <div class="form-group">

                    <label>
                        Transaction Type
                    </label>


                    <select
                        name="transaction_type"
                        required
                    >


                        <option
                            value="IN"
                            <?php
                            echo (
                                $transaction[
                                    "transaction_type"
                                ] === "IN"
                            )
                                ? "selected"
                                : "";
                            ?>
                        >
                            Stock In
                        </option>


                        <option
                            value="OUT"
                            <?php
                            echo (
                                $transaction[
                                    "transaction_type"
                                ] === "OUT"
                            )
                                ? "selected"
                                : "";
                            ?>
                        >
                            Stock Out
                        </option>


                    </select>

                </div>


                <!-- QUANTITY -->

                <div class="form-group">

                    <label>
                        Quantity
                    </label>


                    <input
                        type="number"
                        name="quantity"
                        step="0.01"
                        min="0.01"
                        value="<?php
                            echo htmlspecialchars(
                                $transaction[
                                    "quantity"
                                ]
                            );
                        ?>"
                        required
                    >

                </div>


                <!-- REMARKS -->

                <div class="form-group">

                    <label>
                        Remarks
                    </label>


                    <input
                        type="text"
                        name="remarks"
                        value="<?php
                            echo htmlspecialchars(
                                $transaction[
                                    "remarks"
                                ]
                            );
                        ?>"
                        placeholder="Optional remarks"
                    >

                </div>


            </div>


            <!-- WARNING -->

            <div class="calculation-box">

                <strong>
                    Important:
                </strong>

                Editing this transaction will
                automatically recalculate the
                ingredient's current stock quantity.

            </div>


            <!-- BUTTONS -->

            <div
                style="
                    display:flex;
                    gap:10px;
                    margin-top:20px;
                "
            >


                <button
                    type="submit"
                    class="login-btn"
                >
                    Update Stock Transaction
                </button>


                <a
                    href="stock.php"
                    class="back-dashboard-btn"
                    style="
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        text-decoration:none;
                    "
                >
                    Cancel
                </a>


            </div>


        </form>


    </div>


</main>


</body>

</html>