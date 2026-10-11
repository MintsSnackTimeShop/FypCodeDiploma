<?php

require_once "includes/auth.php";
requireRole(["admin", "manager"]);

require_once "config/database.php";

$message = "";
$message_type = "";


/*CHECK COST ID*/

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {

    header("Location: costs.php");
    exit();

}

$id = intval($_GET["id"]);


/* UPDATE COST*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $ingredient_id =
        intval($_POST["ingredient_id"]);

    $quantity =
        floatval($_POST["quantity"]);

    $unit_cost =
        floatval($_POST["unit_cost"]);


    /* Validate */

    if (
        $ingredient_id <= 0 ||
        $quantity <= 0 ||
        $unit_cost < 0
    ) {

        $message =
            "Please enter valid cost information.";

        $message_type = "error";

    } else {


        /* Calculate total */

        $total_cost =
            $quantity * $unit_cost;


        /* Update */

        $sql = "
            UPDATE costs

            SET
                ingredient_id = ?,
                quantity = ?,
                unit_cost = ?,
                total_cost = ?

            WHERE id = ?
        ";


        $stmt =
            $conn->prepare($sql);


        $stmt->bind_param(
            "idddi",
            $ingredient_id,
            $quantity,
            $unit_cost,
            $total_cost,
            $id
        );


        if ($stmt->execute()) {

            header(
                "Location: costs.php?updated=1"
            );

            exit();

        } else {

            $message =
                "Failed to update cost record.";

            $message_type = "error";
        }
    }
}


/*GET CURRENT COST RECORD */

$sql = "
    SELECT
        id,
        ingredient_id,
        quantity,
        unit_cost,
        total_cost

    FROM costs

    WHERE id = ?
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

    header("Location: costs.php");
    exit();

}


$cost =
    $result->fetch_assoc();


/* GET INGREDIENTS */

$ingredients = $conn->query("
    SELECT
        id,
        ingredient_name,
        unit,
        cost_per_unit

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
        Edit Cost - StockIt
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


    <!-- PAGE HEADER -->

    <div class="page-header">


        <div>

            <p class="page-label">
                FINANCE
            </p>

            <h1>
                Edit Cost Record
            </h1>

        </div>


        <a
            href="costs.php"
            class="back-dashboard-btn"
        >
            ← Back to Cost Management
        </a>


    </div>


    <!-- EDIT FORM-->

    <div class="modern-card">


        <div class="card-heading">

            <div>

                <p class="card-label">
                    FINANCE
                </p>

                <h2>
                    Update Cost Details
                </h2>

            </div>

        </div>


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


        <form method="POST">


            <div class="form-row">


                <!-- INGREDIENT -->

                <div class="form-group">

                    <label>
                        Ingredient
                    </label>


                    <select
                        name="ingredient_id"
                        id="ingredient_id"
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

                                data-cost="<?php
                                    echo $ingredient[
                                        "cost_per_unit"
                                    ];
                                ?>"

                                <?php

                                echo (
                                    $ingredient["id"]
                                    ==
                                    $cost["ingredient_id"]
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

                            </option>


                        <?php endwhile; ?>


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
                        id="cost_quantity"
                        step="0.01"
                        min="0.01"
                        value="<?php
                            echo htmlspecialchars(
                                $cost["quantity"]
                            );
                        ?>"
                        required
                    >

                </div>


                <!-- UNIT COST -->

                <div class="form-group">

                    <label>
                        Cost Per Unit (RM)
                    </label>


                    <input
                        type="number"
                        name="unit_cost"
                        id="unit_cost"
                        step="0.01"
                        min="0"
                        value="<?php
                            echo htmlspecialchars(
                                $cost["unit_cost"]
                            );
                        ?>"
                        required
                    >

                </div>


            </div>


            <!-- TOTAL -->

            <div class="calculation-box">

                <strong>
                    Total Cost:
                </strong>

                RM

                <span id="total_cost">

                    <?php
                    echo number_format(
                        $cost["total_cost"],
                        2
                    );
                    ?>

                </span>

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
                    Update Cost Record
                </button>


                <a
                    href="costs.php"
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