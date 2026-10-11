<?php

require_once "includes/auth.php";
requireRole(["admin", "manager", "staff"]);

require_once "config/database.php";

$message = "";
$message_type = "";


/*CHECK INGREDIENT ID*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: ingredients.php");
    exit();

}

$id = intval($_GET["id"]);


/*UPDATE INGREDIENT*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $ingredient_name =
        trim($_POST["ingredient_name"]);

    $unit =
        trim($_POST["unit"]);

    $quantity =
        floatval($_POST["quantity"]);

    $minimum_stock =
        floatval($_POST["minimum_stock"]);

    $cost_per_unit =
        floatval($_POST["cost_per_unit"]);


    /*
     * Validate
     */

    if (
        empty($ingredient_name) ||
        empty($unit) ||
        $quantity < 0 ||
        $minimum_stock < 0 ||
        $cost_per_unit < 0
    ) {

        $message =
            "Please enter valid ingredient information.";

        $message_type = "error";

    } else {


        /*
         * Update database
         */

        $sql = "
            UPDATE ingredients

            SET
                ingredient_name = ?,
                unit = ?,
                quantity = ?,
                minimum_stock = ?,
                cost_per_unit = ?

            WHERE id = ?
        ";


        $stmt = $conn->prepare($sql);


        $stmt->bind_param(
            "ssdddi",
            $ingredient_name,
            $unit,
            $quantity,
            $minimum_stock,
            $cost_per_unit,
            $id
        );


        if ($stmt->execute()) {

            /*
             * Return to Ingredients page
             */

            header(
                "Location: ingredients.php?updated=1"
            );

            exit();

        } else {

            $message =
                "Failed to update ingredient.";

            $message_type = "error";
        }
    }
}


/*GET INGREDIENT*/

$sql = "
    SELECT
        id,
        ingredient_name,
        unit,
        quantity,
        minimum_stock,
        cost_per_unit

    FROM ingredients

    WHERE id = ?
";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    header("Location: ingredients.php");
    exit();

}


$ingredient =
    $result->fetch_assoc();

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
        Edit Ingredient - StockIt
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


    <!-- SIDEBAR-->

    <?php include "includes/sidebar.php"; ?>


    <!--MAIN CONTENT-->

    <main class="main-content">


        <!-- TOP BAR-->

        <div class="topbar">


            <div>

                <p class="page-label">
                    INVENTORY
                </p>

                <h1>
                    Edit Ingredient
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
                    Edit Ingredient
                </h1>

            </div>


            <a
                href="ingredients.php"
                class="back-dashboard-btn"
            >
                ← Back to Ingredients
            </a>


        </div>


        <!-- DESCRIPTION-->

        <div class="section-heading">

            <p>
                INVENTORY
            </p>

            <h2>
                Update Ingredient Details
            </h2>

        </div>


        <!--MESSAGE-->

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


        <!-- EDIT FORM-->

        <div class="modern-card">


            <div class="card-heading">


                <div>

                    <p class="card-label">
                        INGREDIENT
                    </p>

                    <h2>
                        Edit Ingredient Details
                    </h2>

                </div>


            </div>


            <form method="POST">


                <div class="form-row">


                    <!-- INGREDIENT NAME -->

                    <div class="form-group">

                        <label>
                            Ingredient Name
                        </label>

                        <input
                            type="text"
                            name="ingredient_name"
                            value="<?php
                                echo htmlspecialchars(
                                    $ingredient[
                                        "ingredient_name"
                                    ]
                                );
                            ?>"
                            placeholder="Ingredient name"
                            required
                        >

                    </div>


                    <!-- UNIT -->

                    <div class="form-group">

                        <label>
                            Unit
                        </label>

                        <select
                            name="unit"
                            required
                        >

                            <option value="">
                                Select Unit
                            </option>


                            <option
                                value="kg"
                                <?php
                                echo $ingredient["unit"] === "kg"
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Kilogram (kg)
                            </option>


                            <option
                                value="g"
                                <?php
                                echo $ingredient["unit"] === "g"
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Gram (g)
                            </option>


                            <option
                                value="L"
                                <?php
                                echo $ingredient["unit"] === "L"
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Liter (L)
                            </option>


                            <option
                                value="ml"
                                <?php
                                echo $ingredient["unit"] === "ml"
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Milliliter (ml)
                            </option>


                            <option
                                value="pcs"
                                <?php
                                echo $ingredient["unit"] === "pcs"
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Pieces
                            </option>


                        </select>

                    </div>


                    <!-- QUANTITY -->

                    <div class="form-group">

                        <label>
                            Current Quantity
                        </label>

                        <input
                            type="number"
                            name="quantity"
                            step="0.01"
                            min="0"
                            value="<?php
                                echo htmlspecialchars(
                                    $ingredient[
                                        "quantity"
                                    ]
                                );
                            ?>"
                            required
                        >

                    </div>


                    <!-- MINIMUM STOCK -->

                    <div class="form-group">

                        <label>
                            Minimum Stock
                        </label>

                        <input
                            type="number"
                            name="minimum_stock"
                            step="0.01"
                            min="0"
                            value="<?php
                                echo htmlspecialchars(
                                    $ingredient[
                                        "minimum_stock"
                                    ]
                                );
                            ?>"
                            required
                        >

                    </div>


                    <!-- COST -->

                    <div class="form-group">

                        <label>
                            Cost Per Unit (RM)
                        </label>

                        <input
                            type="number"
                            name="cost_per_unit"
                            step="0.01"
                            min="0"
                            value="<?php
                                echo htmlspecialchars(
                                    $ingredient[
                                        "cost_per_unit"
                                    ]
                                );
                            ?>"
                            required
                        >

                    </div>


                </div>


                <!-- CURRENT STOCK STATUS -->

                <div class="calculation-box">

                    <strong>
                        Current Stock:
                    </strong>

                    <?php
                    echo number_format(
                        $ingredient["quantity"],
                        2
                    );
                    ?>

                    <?php
                    echo htmlspecialchars(
                        $ingredient["unit"]
                    );
                    ?>


                    &nbsp;&nbsp;


                    <?php
                    if (
                        $ingredient["quantity"]
                        <=
                        $ingredient["minimum_stock"]
                    ):
                    ?>

                        <span class="stock-low">
                            Low Stock
                        </span>

                    <?php else: ?>

                        <span class="stock-ok">
                            In Stock
                        </span>

                    <?php endif; ?>

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
                        Update Ingredient
                    </button>


                    <a
                        href="ingredients.php"
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