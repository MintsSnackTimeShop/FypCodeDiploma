<?php

require_once "includes/auth.php";
requireRole(["admin", "manager"]);

require_once "config/database.php";

$message = "";
$message_type = "";


/*DELETE COST RECORD*/

if (isset($_GET["delete"])) {

    $id = intval($_GET["delete"]);

    if ($id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM costs WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {

            header("Location: costs.php?deleted=1");
            exit();

        } else {

            $message = "Failed to delete cost record.";
            $message_type = "error";
        }
    }
}


/* DELETE SUCCESS MESSAGE*/

if (isset($_GET["deleted"])) {

    $message = "Cost record deleted successfully.";
    $message_type = "success";
}


/*ADD COST RECORD*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $ingredient_id = intval($_POST["ingredient_id"]);
    $quantity = floatval($_POST["quantity"]);
    $unit_cost = floatval($_POST["unit_cost"]);

    if (
        $ingredient_id <= 0 ||
        $quantity <= 0 ||
        $unit_cost < 0
    ) {

        $message = "Please enter valid cost information.";
        $message_type = "error";

    } else {

        $total_cost = $quantity * $unit_cost;

        $sql = "
            INSERT INTO costs
            (
                ingredient_id,
                quantity,
                unit_cost,
                total_cost
            )
            VALUES (?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "iddd",
            $ingredient_id,
            $quantity,
            $unit_cost,
            $total_cost
        );

        if ($stmt->execute()) {

            $message =
                "Cost record added successfully.";

            $message_type = "success";

        } else {

            $message =
                "Failed to add cost record.";

            $message_type = "error";
        }
    }
}


/*TOTAL RECORDED COST*/

$result = $conn->query(
    "SELECT COALESCE(SUM(total_cost), 0) AS total
     FROM costs"
);

$total_cost =
    $result->fetch_assoc()["total"];


/*INGREDIENT LIST*/

$ingredients = $conn->query(
    "SELECT
        id,
        ingredient_name,
        unit,
        cost_per_unit
     FROM ingredients
     ORDER BY ingredient_name ASC"
);


/* COST HISTORY*/

$cost_history = $conn->query(
    "SELECT
        costs.id,
        ingredients.ingredient_name,
        ingredients.unit,
        costs.quantity,
        costs.unit_cost,
        costs.total_cost,
        costs.cost_date

     FROM costs

     INNER JOIN ingredients
        ON costs.ingredient_id =
           ingredients.id

     ORDER BY
        costs.cost_date DESC"
);

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
        Cost Management - StockIt
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


    <!--TOP BAR-->

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
                FINANCE
            </p>

            <h1>
                Cost Management
            </h1>

        </div>


        <a
            href="dashboard.php"
            class="back-dashboard-btn"
        >
            ← Back to Dashboard
        </a>

    </div>


    <!--DESCRIPTION-->

    <div class="section-heading">

        <p>
            COST CONTROL
        </p>

        <h2>
            Track Ingredient Costs
        </h2>

    </div>


    <!-- MESSAGE -->

    <?php if (!empty($message)): ?>

        <div
            class="<?php
                echo $message_type === "success"
                    ? "success-message"
                    : "error-message";
            ?>"
        >

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <!-- TOTAL COST-->

    <div class="cost-summary">

        <h3>
            Total Recorded Cost
        </h3>

        <h1>
            RM
            <?php
            echo number_format(
                $total_cost,
                2
            );
            ?>
        </h1>

    </div>


    <!-- ADD COST-->

    <div class="modern-card">


        <div class="card-heading">

            <div>

                <p class="card-label">
                    FINANCE
                </p>

                <h2>
                    Add Cost Record
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
                        id="ingredient_id"
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
                                    data-cost="<?php
                                        echo $ingredient[
                                            "cost_per_unit"
                                        ];
                                    ?>"
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

                        <?php endif; ?>

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
                        placeholder="0.00"
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
                        placeholder="0.00"
                        required
                    >

                </div>


            </div>


            <!-- CALCULATION -->

            <div class="calculation-box">

                <strong>
                    Total Cost:
                </strong>

                RM

                <span id="total_cost">
                    0.00
                </span>

            </div>


            <button
                type="submit"
                class="login-btn"
            >
                Save Cost Record
            </button>


        </form>

    </div>


    <!--COST HISTORY -->

    <div class="modern-card">


        <div class="card-heading">

            <div>

                <p class="card-label">
                    RECORDS
                </p>

                <h2>
                    Cost History
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
                            Quantity
                        </th>

                        <th>
                            Unit Cost
                        </th>

                        <th>
                            Total Cost
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
                        $cost_history &&
                        $cost_history->num_rows > 0
                    ): ?>


                        <?php
                        while (
                            $row =
                            $cost_history->fetch_assoc()
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


                                <!-- UNIT COST -->

                                <td>

                                    RM

                                    <?php
                                    echo number_format(
                                        $row["unit_cost"],
                                        2
                                    );
                                    ?>

                                </td>


                                <!-- TOTAL -->

                                <td>

                                    <strong>

                                        RM

                                        <?php
                                        echo number_format(
                                            $row["total_cost"],
                                            2
                                        );
                                        ?>

                                    </strong>

                                </td>


                                <!-- DATE -->

                                <td>

                                    <?php
                                    echo date(
                                        "d M Y, h:i A",
                                        strtotime(
                                            $row["cost_date"]
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
                                            href="edit_cost.php?id=<?php echo $row["id"]; ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>


                                        <a
                                            href="costs.php?delete=<?php echo $row["id"]; ?>"
                                            class="delete-btn"
                                            onclick="return confirm('Are you sure you want to delete this cost record?');"
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

                                No cost records found.

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