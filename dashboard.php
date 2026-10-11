<?php

require_once "includes/auth.php";
require_once "config/database.php";

// DASHBOARD STATISTICS

// Total ingredients
$result = $conn->query(
    "SELECT COUNT(*) AS total FROM ingredients"
);

$total_ingredients = $result->fetch_assoc()["total"];


// Low stock ingredients
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM ingredients
     WHERE quantity <= minimum_stock"
);

$low_stock = $result->fetch_assoc()["total"];


// Total recorded cost
$result = $conn->query(
    "SELECT COALESCE(SUM(total_cost), 0) AS total
     FROM costs"
);

$total_cost = $result->fetch_assoc()["total"];


// Total stock quantity
$result = $conn->query(
    "SELECT COALESCE(SUM(quantity), 0) AS total
     FROM ingredients"
);

$total_stock = $result->fetch_assoc()["total"];


// Recent stock transactions
$recent_transactions = $conn->query(
    "SELECT
        stock_transactions.*,
        ingredients.ingredient_name,
        ingredients.unit
     FROM stock_transactions
     INNER JOIN ingredients
        ON stock_transactions.ingredient_id = ingredients.id
     ORDER BY stock_transactions.transaction_date DESC
     LIMIT 5"
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

    <title>Dashboard - StockIt</title>

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


    <!-- TOP BAR -->

    <header class="topbar">

        <div>

            <p class="page-label">
                RESTAURANT MANAGEMENT
            </p>

            <h1>
                Dashboard
            </h1>

        </div>


        <div class="topbar-user">

            <div class="topbar-avatar">

                <?php
                echo strtoupper(
                    substr($_SESSION["name"], 0, 1)
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

    </header>


    <!-- HERO -->

    <section class="dashboard-hero">

        <div class="hero-overlay">

            <div>

                <p class="hero-small">
                    WELCOME BACK
                </p>

                <h2>
                    Hello,
                    <?php
                    echo htmlspecialchars(
                        $_SESSION["name"]
                    );
                    ?>!
                </h2>

                <p>
                    Manage your restaurant ingredients,
                    stock and costs from one place.
                </p>

            </div>

        </div>

    </section>


    <!-- STATISTICS -->

    <section class="statistics-grid">


        <div class="stat-card">

            <div class="stat-icon ingredients-stat">
                🥬
            </div>

            <div>

                <span>
                    Total Ingredients
                </span>

                <h2>
                    <?php echo $total_ingredients; ?>
                </h2>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon stock-stat">
                📦
            </div>

            <div>

                <span>
                    Total Stock
                </span>

                <h2>
                    <?php echo number_format($total_stock, 2); ?>
                </h2>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon warning-stat">
                ⚠
            </div>

            <div>

                <span>
                    Low Stock Items
                </span>

                <h2>
                    <?php echo $low_stock; ?>
                </h2>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon cost-stat">
                RM
            </div>

            <div>

                <span>
                    Recorded Cost
                </span>

                <h2>
                    RM <?php echo number_format($total_cost, 2); ?>
                </h2>

            </div>

        </div>


    </section>


    <!-- MANAGEMENT CARDS -->

    <section>

        <div class="section-heading">

            <div>

                <p>
                    MANAGEMENT
                </p>

                <h2>
                    Quick Access
                </h2>

            </div>

        </div>


        <div class="professional-cards">


            <!-- INGREDIENTS -->

            <a
                href="ingredients.php"
                class="professional-card"
            >

                <div class="card-image-container">

                    <img
                        src="images/ingredients.jpg"
                        alt="Restaurant Ingredients"
                        class="card-image"
                    >

                </div>

                <div class="professional-card-content">

                    <span class="card-category">
                        INVENTORY
                    </span>

                    <h3>
                        Ingredients
                    </h3>

                    <p>
                        Manage ingredients, quantities,
                        minimum stock and unit costs.
                    </p>

                    <span class="card-link">
                        Manage Ingredients →
                    </span>

                </div>

            </a>


            <!-- STOCK -->

            <a
                href="stock.php"
                class="professional-card"
            >

                <div class="card-image-container">

                    <img
                        src="images/stock.jpg"
                        alt="Restaurant Stock"
                        class="card-image"
                    >

                </div>

                <div class="professional-card-content">

                    <span class="card-category">
                        INVENTORY
                    </span>

                    <h3>
                        Stock Management
                    </h3>

                    <p>
                        Record stock in and stock out
                        transactions.
                    </p>

                    <span class="card-link">
                        Manage Stock →
                    </span>

                </div>

            </a>


            <!-- COST -->

            <?php if (
                $_SESSION["role"] == "admin" ||
                $_SESSION["role"] == "manager"
            ): ?>

                <a
                    href="costs.php"
                    class="professional-card"
                >

                    <div class="card-image-container">

                        <img
                            src="images/costs.jpg"
                            alt="Restaurant Cost Management"
                            class="card-image"
                        >

                    </div>

                    <div class="professional-card-content">

                        <span class="card-category">
                            FINANCE
                        </span>

                        <h3>
                            Cost Management
                        </h3>

                        <p>
                            Record ingredient costs and
                            monitor restaurant expenses.
                        </p>

                        <span class="card-link">
                            Manage Costs →
                        </span>

                    </div>

                </a>

            <?php endif; ?>


        </div>

    </section>


    <!-- RECENT TRANSACTIONS -->

    <section class="recent-section">

        <div class="section-heading">

            <div>

                <p>
                    ACTIVITY
                </p>

                <h2>
                    Recent Stock Transactions
                </h2>

            </div>

        </div>


        <div class="recent-table-card">

            <?php if ($recent_transactions->num_rows > 0): ?>

                <div class="table-responsive">

                    <table>

                        <thead>

                            <tr>

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

                            </tr>

                        </thead>


                        <tbody>

                        <?php while (
                            $transaction =
                            $recent_transactions->fetch_assoc()
                        ): ?>

                            <tr>

                                <td>

                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $transaction[
                                                "ingredient_name"
                                            ]
                                        );
                                        ?>
                                    </strong>

                                </td>


                                <td>

                                    <?php if (
                                        $transaction[
                                            "transaction_type"
                                        ] == "IN"
                                    ): ?>

                                        <span class="stock-in">
                                            STOCK IN
                                        </span>

                                    <?php else: ?>

                                        <span class="stock-out">
                                            STOCK OUT
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php
                                    echo number_format(
                                        $transaction["quantity"],
                                        2
                                    );
                                    ?>

                                    <?php
                                    echo htmlspecialchars(
                                        $transaction["unit"]
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $transaction["remarks"]
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo date(
                                        "d M Y, h:i A",
                                        strtotime(
                                            $transaction[
                                                "transaction_date"
                                            ]
                                        )
                                    );
                                    ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-state">

                    <div>
                        📦
                    </div>

                    <h3>
                        No stock activity yet
                    </h3>

                    <p>
                        Stock transactions will appear here
                        when you record stock movements.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </section>


</main>


</body>
</html>