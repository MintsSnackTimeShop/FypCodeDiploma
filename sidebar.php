<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">

    <div class="sidebar-brand">

        <div class="brand-logo">
            S
        </div>

        <div>
            <h2>StockIt</h2>
            <span>Restaurant System</span>
        </div>

    </div>


    <div class="sidebar-user">

        <div class="user-avatar">
            <?php echo strtoupper(substr($_SESSION["name"], 0, 1)); ?>
        </div>

        <div class="user-details">

            <strong>
                <?php echo htmlspecialchars($_SESSION["name"]); ?>
            </strong>

            <span>
                <?php echo ucfirst(htmlspecialchars($_SESSION["role"])); ?>
            </span>

        </div>

    </div>


    <nav class="sidebar-nav">

        <p class="nav-title">MAIN MENU</p>

        <a
            href="dashboard.php"
            class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>"
        >
            <span class="nav-icon">⌂</span>
            Dashboard
        </a>


        <a
            href="ingredients.php"
            class="<?php echo $current_page == 'ingredients.php' ? 'active' : ''; ?>"
        >
            <span class="nav-icon">🥬</span>
            Ingredients
        </a>


        <a
            href="stock.php"
            class="<?php echo $current_page == 'stock.php' ? 'active' : ''; ?>"
        >
            <span class="nav-icon">📦</span>
            Stock Management
        </a>


        <?php if (
            $_SESSION["role"] == "admin" ||
            $_SESSION["role"] == "manager"
        ): ?>

            <a
                href="costs.php"
                class="<?php echo $current_page == 'costs.php' ? 'active' : ''; ?>"
            >
                <span class="nav-icon">💰</span>
                Cost Management
            </a>

        <?php endif; ?>


        <?php if ($_SESSION["role"] == "admin"): ?>

            <p class="nav-title">ADMINISTRATION</p>

            <a href="register.php">
                <span class="nav-icon">👤</span>
                Register User
            </a>

        <?php endif; ?>


       <p class="nav-title">ACCOUNT</p>

<a href="help.php"
   class="<?php echo $current_page == 'help.php' ? 'active' : ''; ?>">

    <span class="nav-icon">?</span>
    Help & User Guide

</a>

<a href="logout.php" class="logout-link">
    <span class="nav-icon">↪</span>
    Logout
</a>

    </nav>

</aside>