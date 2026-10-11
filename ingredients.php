<?php
require_once "includes/auth.php";
requireRole(["admin", "manager", "staff"]);

require_once "config/database.php";

$message = "";
$message_type = "";


/*ADD INGREDIENT */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $ingredient_name = trim($_POST["ingredient_name"]);
    $unit = trim($_POST["unit"]);
    $quantity = floatval($_POST["quantity"]);
    $minimum_stock = floatval($_POST["minimum_stock"]);
    $cost_per_unit = floatval($_POST["cost_per_unit"]);

    if (
        empty($ingredient_name) ||
        empty($unit) ||
        $quantity < 0 ||
        $minimum_stock < 0 ||
        $cost_per_unit < 0
    ) {

        $message = "Please enter valid ingredient information.";
        $message_type = "error";

    } else {

        $sql = "INSERT INTO ingredients
                (ingredient_name, unit, quantity, minimum_stock, cost_per_unit)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ssddd",
            $ingredient_name,
            $unit,
            $quantity,
            $minimum_stock,
            $cost_per_unit
        );

        if ($stmt->execute()) {

            $message = "Ingredient added successfully.";
            $message_type = "success";

        } else {

            $message = "Failed to add ingredient.";
            $message_type = "error";
        }
    }
}


/* DELETE INGREDIENT*/

if (isset($_GET["delete"])) {

    $id = intval($_GET["delete"]);

    $stmt = $conn->prepare(
        "DELETE FROM ingredients WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {

        header("Location: ingredients.php?deleted=1");
        exit();

    } else {

        $message = "Failed to delete ingredient.";
        $message_type = "error";
    }
}


/* SUCCESS MESSAGE*/

if (isset($_GET["deleted"])) {

    $message = "Ingredient deleted successfully.";
    $message_type = "success";
}


/* SEARCH*/

$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

if ($search !== "") {

    $stmt = $conn->prepare(
        "SELECT *
         FROM ingredients
         WHERE ingredient_name LIKE ?
         ORDER BY ingredient_name ASC"
    );

    $search_value = "%" . $search . "%";

    $stmt->bind_param("s", $search_value);

    $stmt->execute();

    $ingredients = $stmt->get_result();

} else {

    $ingredients = $conn->query(
        "SELECT *
         FROM ingredients
         ORDER BY ingredient_name ASC"
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Ingredients - StockIt</title>

    <link rel="stylesheet" href="css/style.css">

    <script src="js/script.js" defer></script>

</head>

<body class="app-body">

<?php include "includes/sidebar.php"; ?>


<main class="main-content">


    <!-- TOP BAR -->
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

    </div>


    <!-- PAGE HEADER -->

    <div class="page-header">

        <div>

            <p class="page-label">
                INVENTORY
            </p>

            <h1>
                Ingredients Management
            </h1>

        </div>

        <a
            href="dashboard.php"
            class="back-dashboard-btn"
        >
            ← Back to Dashboard
        </a>

    </div>


    <!-- MESSAGE -->

    <?php if (!empty($message)): ?>

        <div class="<?php
            echo $message_type === "success"
                ? "success-message"
                : "error-message";
        ?>">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <!-- ADD INGREDIENT -->

    <div class="modern-card">

        <div class="card-heading">

            <div>

                <p class="card-label">
                    INVENTORY
                </p>

                <h2>
                    Add Ingredient
                </h2>

            </div>

        </div>


        <form method="POST">

            <div class="form-row">


                <div class="form-group">

                    <label>
                        Ingredient Name
                    </label>

                    <input
                        type="text"
                        name="ingredient_name"
                        placeholder="e.g. Chicken"
                        required
                    >

                </div>


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

                        <option value="kg">
                            Kilogram (kg)
                        </option>

                        <option value="g">
                            Gram (g)
                        </option>

                        <option value="L">
                            Liter (L)
                        </option>

                        <option value="ml">
                            Milliliter (ml)
                        </option>

                        <option value="pcs">
                            Pieces
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        Quantity
                    </label>

                    <input
                        type="number"
                        name="quantity"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Minimum Stock
                    </label>

                    <input
                        type="number"
                        name="minimum_stock"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Cost Per Unit (RM)
                    </label>

                    <input
                        type="number"
                        name="cost_per_unit"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        required
                    >

                </div>


            </div>


            <button
                type="submit"
                class="login-btn"
            >
                Add Ingredient
            </button>

        </form>

    </div>


    <!-- SEARCH -->

    <div class="modern-card">

        <div class="card-heading">

            <div>

                <p class="card-label">
                    SEARCH
                </p>

                <h2>
                    Find Ingredient
                </h2>

            </div>

        </div>


        <form
            method="GET"
            class="search-box"
        >

            <input
                type="text"
                name="search"
                value="<?php
                    echo htmlspecialchars($search);
                ?>"
                placeholder="Search ingredient..."
            >

            <button
                type="submit"
                class="login-btn"
            >
                Search
            </button>

        </form>

    </div>


    <!-- INGREDIENT TABLE -->

    <div class="modern-card">

        <div class="card-heading">

            <div>

                <p class="card-label">
                    INVENTORY
                </p>

                <h2>
                    Ingredient List
                </h2>

            </div>

        </div>


        <div class="table-responsive">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Ingredient</th>

                        <th>Unit</th>

                        <th>Quantity</th>

                        <th>Minimum Stock</th>

                        <th>Cost / Unit</th>

                        <th>Status</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>

                <?php if (
                    $ingredients &&
                    $ingredients->num_rows > 0
                ): ?>

                    <?php while (
                        $row =
                        $ingredients->fetch_assoc()
                    ): ?>

                        <tr>

                            <td>
                                <?php
                                echo $row["id"];
                                ?>
                            </td>

                            <td>
                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["ingredient_name"]
                                    );
                                    ?>
                                </strong>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row["unit"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo number_format(
                                    $row["quantity"],
                                    2
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo number_format(
                                    $row["minimum_stock"],
                                    2
                                );
                                ?>
                            </td>

                            <td>
                                RM
                                <?php
                                echo number_format(
                                    $row["cost_per_unit"],
                                    2
                                );
                                ?>
                            </td>

                            <td>

                                <?php if (
                                    $row["quantity"]
                                    <=
                                    $row["minimum_stock"]
                                ): ?>

                                    <span class="stock-low">
                                        Low Stock
                                    </span>

                                <?php else: ?>

                                    <span class="stock-ok">
                                        In Stock
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <a
                                    href="edit_ingredient.php?id=<?php echo $row["id"]; ?>"
                                    class="edit-btn"
                                >
                                    Edit
                                </a>

                                <a
                                    href="ingredients.php?delete=<?php echo $row["id"]; ?>"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this ingredient?');"
                                >
                                    Delete
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="8"
                            class="empty-state"
                        >
                            No ingredients found.
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