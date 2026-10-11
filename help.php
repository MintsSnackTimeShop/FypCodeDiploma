<?php
require_once "includes/auth.php";

$current_role = $_SESSION["role"] ?? "staff";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Help & User Guide - StockIt</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body class="app-body">

<?php include "includes/sidebar.php"; ?>

<main class="main-content">

    <!-- Top Bar -->
    <div class="topbar">
        <div>
            <strong>StockIt</strong>
            <span>Restaurant Management System</span>
        </div>

        <div>
            Welcome,
            <strong>
                <?php echo htmlspecialchars($_SESSION["name"]); ?>
            </strong>
        </div>
    </div>


    <!-- Page Header -->
    <div class="page-header">
        <div>
            <p class="page-label">USER SUPPORT</p>
            <h1>Help & User Guide</h1>

            <p class="page-description">
                Learn how to use the main features of the StockIt
                Restaurant Management System.
            </p>
        </div>

        <a href="dashboard.php" class="back-dashboard-btn">
            ← Back to Dashboard
        </a>
    </div>


    <!-- Introduction -->
    <section class="help-welcome-card">

        <div class="help-welcome-icon">
            ?
        </div>

        <div>
            <h2>Welcome to StockIt Help</h2>

            <p>
                This guide explains how to manage ingredients,
                update restaurant stock, record ingredient costs,
                monitor low-stock items and manage your account.
            </p>

            <p>
                Your current account role:
                <strong>
                    <?php echo ucfirst(htmlspecialchars($current_role)); ?>
                </strong>
            </p>
        </div>

    </section>


    <!-- Quick Navigation -->
    <section class="help-section">

        <div class="help-section-header">
            <h2>Quick Guide</h2>
            <p>Select a section to learn how to use StockIt.</p>
        </div>

        <div class="help-navigation">

            <a href="#dashboard" class="help-nav-card">
                <span class="help-nav-icon">⌂</span>
                <strong>Dashboard</strong>
                <small>System overview</small>
            </a>

            <a href="#ingredients" class="help-nav-card">
                <span class="help-nav-icon">🥬</span>
                <strong>Ingredients</strong>
                <small>Manage ingredients</small>
            </a>

            <a href="#stock" class="help-nav-card">
                <span class="help-nav-icon">📦</span>
                <strong>Stock</strong>
                <small>Stock IN & OUT</small>
            </a>

            <?php if ($current_role == "admin" || $current_role == "manager"): ?>

            <a href="#cost" class="help-nav-card">
                <span class="help-nav-icon">💰</span>
                <strong>Cost</strong>
                <small>Record food costs</small>
            </a>

            <?php endif; ?>

            <a href="#low-stock" class="help-nav-card">
                <span class="help-nav-icon">⚠</span>
                <strong>Low Stock</strong>
                <small>Monitor inventory</small>
            </a>

            <a href="#account" class="help-nav-card">
                <span class="help-nav-icon">👤</span>
                <strong>Account</strong>
                <small>Roles & logout</small>
            </a>

        </div>

    </section>


    <!-- Dashboard -->
    <section class="help-guide-card" id="dashboard">

        <div class="help-guide-number">
            01
        </div>

        <div class="help-guide-content">

            <h2>Dashboard</h2>

            <p>
                The Dashboard provides a quick overview of restaurant
                stock and ingredient information.
            </p>

            <div class="help-steps">

                <div class="help-step">
                    <span>1</span>

                    <div>
                        <strong>Login to StockIt</strong>
                        <p>
                            Enter your registered username and password.
                        </p>
                    </div>
                </div>


                <div class="help-step">
                    <span>2</span>

                    <div>
                        <strong>View Dashboard Statistics</strong>
                        <p>
                            The dashboard displays total ingredients,
                            current stock quantity, low-stock ingredients
                            and recorded costs.
                        </p>
                    </div>
                </div>


                <div class="help-step">
                    <span>3</span>

                    <div>
                        <strong>View Recent Transactions</strong>
                        <p>
                            Recent Stock IN and Stock OUT transactions
                            are displayed for quick monitoring.
                        </p>
                    </div>
                </div>

            </div>

            <a href="dashboard.php" class="help-action-btn">
                Open Dashboard →
            </a>

        </div>

    </section>


    <!-- Ingredients -->
    <section class="help-guide-card" id="ingredients">

        <div class="help-guide-number">
            02
        </div>

        <div class="help-guide-content">

            <h2>Ingredient Management</h2>

            <p>
                The Ingredients page is used to register and maintain
                restaurant ingredient information.
            </p>

            <div class="help-steps">

                <div class="help-step">
                    <span>1</span>

                    <div>
                        <strong>Open Ingredients</strong>
                        <p>
                            Select <b>Ingredients</b> from the sidebar.
                        </p>
                    </div>
                </div>


                <div class="help-step">
                    <span>2</span>

                    <div>
                        <strong>Add an Ingredient</strong>
                        <p>
                            Enter the ingredient name, unit,
                            current quantity, minimum stock level
                            and cost per unit.
                        </p>
                    </div>
                </div>


                <div class="help-step">
                    <span>3</span>

                    <div>
                        <strong>Edit Ingredient Information</strong>
                        <p>
                            Click the <b>Edit</b> button beside an ingredient
                            to update its information.
                        </p>
                    </div>
                </div>


                <div class="help-step">
                    <span>4</span>

                    <div>
                        <strong>Delete an Ingredient</strong>
                        <p>
                            Click <b>Delete</b> and confirm the action
                            when an ingredient is no longer required.
                        </p>
                    </div>
                </div>


                <div class="help-step">
                    <span>5</span>

                    <div>
                        <strong>Search for Ingredients</strong>
                        <p>
                            Use the search field to locate an ingredient
                            quickly.
                        </p>
                    </div>
                </div>

            </div>

            <a href="ingredients.php" class="help-action-btn">
                Open Ingredients →
            </a>

        </div>

    </section>


    <!-- Stock Management -->
    <section class="help-guide-card" id="stock">

        <div class="help-guide-number">
            03
        </div>

        <div class="help-guide-content">

            <h2>Stock Management</h2>

            <p>
                Stock Management records incoming and outgoing
                restaurant ingredient quantities.
            </p>

            <div class="help-info-box">
                <strong>Stock IN</strong>
                increases the quantity of an ingredient.
                <br><br>
                <strong>Stock OUT</strong>
                decreases the quantity of an ingredient.
            </div>

            <div class="help-steps">

                <div class="help-step">
                    <span>1</span>

                    <div>
                        <strong>Select Ingredient</strong>
                        <p>
                            Choose the ingredient for the stock transaction.
                        </p>
                    </div>
                </div>


                <div class="help-step">
                    <span>2</span>

                    <div>
                        <strong>Select Transaction Type</strong>
                        <p>
                            Select <b>IN</b> when receiving stock or
                            <b>OUT</b> when ingredients are being used.
                        </p>
                    </div>
                </div>


                <div class="help-step">
                    <span>3</span>

                    <div>
                        <strong>Enter Quantity</strong>
                        <p>
                            Enter the amount being added or removed.
                        </p>
                    </div>
                </div>


                <div class="help-step">
                    <span>4</span>

                    <div>
                        <strong>Add Remarks</strong>
                        <p>
                            Add optional information explaining the
                            transaction.
                        </p>
                    </div>
                </div>


                <div class="help-step">
                    <span>5</span>

                    <div>
                        <strong>Save Transaction</strong>
                        <p>
                            StockIt automatically updates the ingredient
                            quantity and records the transaction.
                        </p>
                    </div>
                </div>

            </div>

            <div class="help-warning">
                <strong>Important:</strong>
                StockIt prevents a Stock OUT transaction when the requested
                quantity is greater than the available stock.
            </div>

            <a href="stock.php" class="help-action-btn">
                Open Stock Management →
            </a>

        </div>

    </section>


    <!-- Cost Management -->

    <?php if ($current_role == "admin" || $current_role == "manager"): ?>

    <section class="help-guide-card" id="cost">

        <div class="help-guide-number">
            04
        </div>

        <div class="help-guide-content">

            <h2>Cost Management</h2>

            <p>
                Cost Management is available to
                <strong>Manager</strong> and
                <strong>Administrator</strong> accounts.
            </p>

            <div class="help-steps">

                <div class="help-step">
                    <span>1</span>

                    <div>
                        <strong>Select Ingredient</strong>
                        <p>
                            Choose the ingredient that will be used
                            for the cost record.
                        </p>
                    </div>
                </div>


                <div class="help-step">
                    <span>2</span>

                    <div>
                        <strong>Enter Quantity</strong>
                        <p>
                            Enter the quantity of the ingredient used.
                        </p>
                    </div>
                </div>


                <div class="help-step">
                    <span>3</span>

                    <div>
                        <strong>Enter Unit Cost</strong>
                        <p>
                            The ingredient's stored cost can be automatically
                            entered into the Unit Cost field.
                        </p>
                    </div>
                </div>


                <div class="help-step">
                    <span>4</span>

                    <div>
                        <strong>Calculate Total Cost</strong>
                        <p>
                            StockIt calculates:
                            <b>Quantity × Unit Cost = Total Cost</b>.
                        </p>
                    </div>
                </div>


                <div class="help-step">
                    <span>5</span>

                    <div>
                        <strong>Save Cost Record</strong>
                        <p>
                            The cost record is stored in the database
                            and displayed in the cost history.
                        </p>
                    </div>
                </div>

            </div>

            <a href="costs.php" class="help-action-btn">
                Open Cost Management →
            </a>

        </div>

    </section>

    <?php endif; ?>


    <!-- Low Stock -->
    <section class="help-guide-card" id="low-stock">

        <div class="help-guide-number">
            <?php echo ($current_role == "staff") ? "04" : "05"; ?>
        </div>

        <div class="help-guide-content">

            <h2>Low-Stock Monitoring</h2>

            <p>
                StockIt helps users identify ingredients that need
                to be restocked.
            </p>

            <div class="help-info-box">

                An ingredient is considered
                <strong>Low Stock</strong> when:

                <div class="help-formula">
                    Current Quantity ≤ Minimum Stock
                </div>

            </div>

            <p>
                Low-stock information is displayed on the Dashboard
                and in the Ingredients section so restaurant staff
                can respond quickly.
            </p>

        </div>

    </section>


    <!-- Account Roles -->
    <section class="help-guide-card" id="account">

        <div class="help-guide-number">
            <?php echo ($current_role == "staff") ? "05" : "06"; ?>
        </div>

        <div class="help-guide-content">

            <h2>User Roles & Account Access</h2>

            <p>
                StockIt uses role-based access control to provide
                suitable functions for different restaurant users.
            </p>

            <div class="role-guide-grid">

                <div class="role-guide-card">
                    <h3>Administrator</h3>

                    <p>Administrator accounts can:</p>

                    <ul>
                        <li>Access the Dashboard</li>
                        <li>Manage Ingredients</li>
                        <li>Manage Stock</li>
                        <li>Manage Costs</li>
                        <li>Register users</li>
                    </ul>
                </div>


                <div class="role-guide-card">
                    <h3>Manager</h3>

                    <p>Manager accounts can:</p>

                    <ul>
                        <li>Access the Dashboard</li>
                        <li>Manage Ingredients</li>
                        <li>Manage Stock</li>
                        <li>Manage Costs</li>
                    </ul>
                </div>


                <div class="role-guide-card">
                    <h3>Staff</h3>

                    <p>Staff accounts can:</p>

                    <ul>
                        <li>Access the Dashboard</li>
                        <li>Manage Ingredients</li>
                        <li>Record Stock IN / OUT</li>
                        <li>Monitor stock levels</li>
                    </ul>
                </div>

            </div>

        </div>

    </section>


    <!-- Logout -->
    <section class="help-guide-card">

        <div class="help-guide-number">
            ✓
        </div>

        <div class="help-guide-content">

            <h2>Logging Out</h2>

            <p>
                When you have finished using StockIt, click
                <strong>Logout</strong> from the sidebar.
            </p>

            <p>
                Logging out ends the current session and returns
                the user to the login page.
            </p>

        </div>

    </section>


    <!-- Support Footer -->
    <section class="help-support">

        <div>
            <h2>Need Assistance?</h2>

            <p>
                If you experience a problem while using StockIt,
                contact the system administrator or restaurant manager.
            </p>
        </div>

        <a href="dashboard.php" class="help-action-btn">
            Return to Dashboard
        </a>

    </section>


</main>

</body>
</html>