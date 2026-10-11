<?php

require_once "includes/auth.php";
requireRole(["admin", "manager", "staff"]);

require_once "config/database.php";

$message = "";
$message_type = "";


/*DELETE STOCK TRANSACTION*/

if (isset($_GET["delete"])) {

    $transaction_id = intval($_GET["delete"]);

    if ($transaction_id > 0) {

        $conn->begin_transaction();

        try {

            /*
             * Get the transaction and lock the ingredient row
             */
            $sql = "
                SELECT
                    stock_transactions.id,
                    stock_transactions.ingredient_id,
                    stock_transactions.transaction_type,
                    stock_transactions.quantity,
                    ingredients.quantity AS current_quantity

                FROM stock_transactions

                INNER JOIN ingredients
                    ON stock_transactions.ingredient_id =
                       ingredients.id

                WHERE stock_transactions.id = ?

                FOR UPDATE
            ";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $transaction_id);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception(
                    "Stock transaction not found."
                );
            }

            $transaction = $result->fetch_assoc();

            $ingredient_id =
                intval($transaction["ingredient_id"]);

            $transaction_type =
                $transaction["transaction_type"];

            $transaction_quantity =
                floatval($transaction["quantity"]);

            $current_quantity =
                floatval($transaction["current_quantity"]);


            if ($transaction_type === "IN") {

                $new_quantity =
                    $current_quantity -
                    $transaction_quantity;

                if ($new_quantity < 0) {

                    throw new Exception(
                        "This transaction cannot be deleted because it would make the stock quantity negative."
                    );
                }

            } else {

                $new_quantity =
                    $current_quantity +
                    $transaction_quantity;
            }


            /*
             * Update ingredient quantity
             */
            $update_sql = "
                UPDATE ingredients

                SET quantity = ?

                WHERE id = ?
            ";

            $update_stmt =
                $conn->prepare($update_sql);

            $update_stmt->bind_param(
                "di",
                $new_quantity,
                $ingredient_id
            );

            if (!$update_stmt->execute()) {

                throw new Exception(
                    "Failed to update ingredient stock."
                );
            }


            /*
             * Delete transaction
             */
            $delete_sql = "
                DELETE FROM stock_transactions
                WHERE id = ?
            ";

            $delete_stmt =
                $conn->prepare($delete_sql);

            $delete_stmt->bind_param(
                "i",
                $transaction_id
            );

            if (!$delete_stmt->execute()) {

                throw new Exception(
                    "Failed to delete stock transaction."
                );
            }


            $conn->commit();

            header(
                "Location: stock.php?deleted=1"
            );

            exit();

        } catch (Exception $e) {

            $conn->rollback();

            $message = $e->getMessage();
            $message_type = "error";
        }
    }
}


/* SUCCESS MESSAGE */

if (isset($_GET["deleted"])) {

    $message =
        "Stock transaction deleted successfully.";

    $message_type = "success";
}

if (isset($_GET["updated"])) {

    $message =
        "Stock transaction updated successfully.";

    $message_type = "success";
}


/*ADD STOCK TRANSACTION */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $ingredient_id =
        intval($_POST["ingredient_id"]);

    $transaction_type =
        $_POST["transaction_type"];

    $quantity =
        floatval($_POST["quantity"]);

    $remarks =
        trim($_POST["remarks"]);


    /*
     * Validate
     */

    if (
        $ingredient_id <= 0 ||
        $quantity <= 0 ||
        (
            $transaction_type !== "IN" &&
            $transaction_type !== "OUT"
        )
    ) {

        $message =
            "Please enter valid stock transaction information.";

        $message_type = "error";

    } else {

        $conn->begin_transaction();

        try {

            /*
             * Lock ingredient row
             */

            $sql = "
                SELECT quantity

                FROM ingredients

                WHERE id = ?

                FOR UPDATE
            ";

            $stmt =
                $conn->prepare($sql);

            $stmt->bind_param(
                "i",
                $ingredient_id
            );

            $stmt->execute();

            $result =
                $stmt->get_result();


            if ($result->num_rows === 0) {

                throw new Exception(
                    "Ingredient not found."
                );
            }


            $ingredient =
                $result->fetch_assoc();


            $current_quantity =
                floatval(
                    $ingredient["quantity"]
                );


            /*
             * Calculate new stock
             */

            if ($transaction_type === "IN") {

                $new_quantity =
                    $current_quantity +
                    $quantity;

            } else {

                if ($quantity > $current_quantity) {

                    throw new Exception(
                        "Not enough stock available. Current stock: "
                        . number_format(
                            $current_quantity,
                            2
                        )
                    );
                }

                $new_quantity =
                    $current_quantity -
                    $quantity;
            }


            /*
             * Update ingredient
             */

            $update_sql = "
                UPDATE ingredients

                SET quantity = ?

                WHERE id = ?
            ";

            $update_stmt =
                $conn->prepare($update_sql);

            $update_stmt->bind_param(
                "di",
                $new_quantity,
                $ingredient_id
            );

            if (!$update_stmt->execute()) {

                throw new Exception(
                    "Failed to update ingredient stock."
                );
            }


            /*
             * Insert transaction
             */

            $insert_sql = "
                INSERT INTO stock_transactions
                (
                    ingredient_id,
                    transaction_type,
                    quantity,
                    remarks
                )

                VALUES (?, ?, ?, ?)
            ";

            $insert_stmt =
                $conn->prepare($insert_sql);

            $insert_stmt->bind_param(
                "isds",
                $ingredient_id,
                $transaction_type,
                $quantity,
                $remarks
            );


            if (!$insert_stmt->execute()) {

                throw new Exception(
                    "Failed to record stock transaction."
                );
            }


            $conn->commit();


            header(
                "Location: stock.php?success=1"
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


/* SUCCESS MESSAGE */

if (isset($_GET["success"])) {

    $message =
        "Stock transaction recorded successfully.";

    $message_type =
        "success";
}


/*GET INGREDIENTS */

$ingredients = $conn->query("
    SELECT
        id,
        ingredient_name,
        unit,
        quantity,
        minimum_stock

    FROM ingredients

    ORDER BY ingredient_name ASC
");


/* GET STOCK HISTORY */

$transactions = $conn->query("
    SELECT
        stock_transactions.id,
        stock_transactions.transaction_type,
        stock_transactions.quantity,
        stock_transactions.transaction_date,
        stock_transactions.remarks,

        ingredients.ingredient_name,
        ingredients.unit

    FROM stock_transactions

    INNER JOIN ingredients
        ON stock_transactions.ingredient_id =
           ingredients.id

    ORDER BY
        stock_transactions.transaction_date DESC
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
        Stock Management - StockIt
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


    <!-- TOP BAR-->

    <div class="topbar">

        <div>

            <p class="page-label">
                
            </p>

            <h1>
               
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
                Stock Management
            </h1>

        </div>


        <a
            href="dashboard.php"
            class="back-dashboard-btn"
        >
            ← Back to Dashboard
        </a>

    </div>


    <!-- DESCRIPTION -->

    <div class="section-heading">

        <p>
            STOCK CONTROL
        </p>

        <h2>
            Manage Restaurant Stock
        </h2>

    </div>


    <!-- MESSAGE-->

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


    <!--ADD STOCK TRANSACTION-->

    <div class="modern-card">


        <div class="card-heading">

            <div>

                <p class="card-label">
                    STOCK
                </p>

                <h2>
                    Add Stock Transaction
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


                        <?php if (
                            $ingredients &&
                            $ingredients->num_rows > 0
                        ): ?>


                            <?php
                            while (
                                $ingredient =
                                $ingredients->fetch_assoc()
                            ):
                            ?>


                                <option
                                    value="<?php
                                        echo $ingredient["id"];
                                    ?>"
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


                        <?php endif; ?>


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

                        <option value="">
                            Select Type
                        </option>

                        <option value="IN">
                            Stock In
                        </option>

                        <option value="OUT">
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
                        placeholder="0.00"
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
                        placeholder="Optional remarks"
                    >

                </div>


            </div>


            <button
                type="submit"
                class="login-btn"
            >
                Save Stock Transaction
            </button>


        </form>


    </div>


    <!-- STOCK HISTORY-->

    <div class="modern-card">


        <div class="card-heading">

            <div>

                <p class="card-label">
                    RECORDS
                </p>

                <h2>
                    Stock Transaction History
                </h2>

            </div>

        </div>


        <div class="table-responsive">


            <table>


                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Ingredient
                        </th>

                        <th>
                            Type
                        </th>

                        <th>
                            Quantity
                        </th>

                        <th>
                            Remarks
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php if (
                        $transactions &&
                        $transactions->num_rows > 0
                    ): ?>


                        <?php
                        while (
                            $row =
                            $transactions->fetch_assoc()
                        ):
                        ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <?php
                                    echo $row["id"];
                                    ?>

                                </td>


                                <!-- INGREDIENT -->

                                <td>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $row[
                                                "ingredient_name"
                                            ]
                                        );
                                        ?>

                                    </strong>

                                </td>


                                <!-- TYPE -->

                                <td>


                                    <?php
                                    if (
                                        $row[
                                            "transaction_type"
                                        ] === "IN"
                                    ):
                                    ?>


                                        <span
                                            class="stock-in"
                                        >
                                            Stock In
                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="stock-out"
                                        >
                                            Stock Out
                                        </span>


                                    <?php endif; ?>


                                </td>


                                <!-- QUANTITY -->

                                <td>

                                    <?php
                                    echo number_format(
                                        $row["quantity"],
                                        2
                                    );
                                    ?>

                                    <?php
                                    echo htmlspecialchars(
                                        $row["unit"]
                                    );
                                    ?>

                                </td>


                                <!-- REMARKS -->

                                <td>

                                    <?php
                                    if (
                                        !empty(
                                            $row["remarks"]
                                        )
                                    ) {

                                        echo htmlspecialchars(
                                            $row["remarks"]
                                        );

                                    } else {

                                        echo "-";
                                    }
                                    ?>

                                </td>


                                <!-- DATE -->

                                <td>

                                    <?php
                                    echo date(
                                        "d M Y, h:i A",
                                        strtotime(
                                            $row[
                                                "transaction_date"
                                            ]
                                        )
                                    );
                                    ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div
                                        class="table-actions"
                                    >

                                        <a
                                            href="edit_stock.php?id=<?php echo $row["id"]; ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>


                                        <a
                                            href="stock.php?delete=<?php echo $row["id"]; ?>"
                                            class="delete-btn"
                                            onclick="return confirm('Are you sure you want to delete this stock transaction? The ingredient stock quantity will also be adjusted.');"
                                        >
                                            Delete
                                        </a>

                                    </div>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="7"
                                class="empty-state"
                            >

                                No stock transactions
                                found.

                            </td>

                        </tr>


                    <?php endif; ?>


                </tbody>

            </table>


        </div>


    </div>


</main>


</body>

</html>