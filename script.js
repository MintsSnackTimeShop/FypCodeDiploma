document.addEventListener("DOMContentLoaded", function () {

    console.log("StockIt JavaScript loaded successfully.");

// COST CALCULATION

    const ingredientSelect =
        document.getElementById("ingredient_id");

    const quantityInput =
        document.getElementById("cost_quantity");

    const unitCostInput =
        document.getElementById("unit_cost");

    const totalCostDisplay =
        document.getElementById("total_cost");


    function calculateCost() {

        if (!quantityInput || !unitCostInput) {
            return;
        }

        const quantity =
            parseFloat(quantityInput.value) || 0;

        const unitCost =
            parseFloat(unitCostInput.value) || 0;

        const total =
            quantity * unitCost;

        totalCostDisplay.textContent =
            total.toFixed(2);
    }


    // Automatically fill unit cost
    // when ingredient is selected

    if (ingredientSelect) {

        ingredientSelect.addEventListener(
            "change",
            function () {

                const selectedOption =
                    this.options[this.selectedIndex];

                const cost =
                    selectedOption.dataset.cost;

                if (cost !== undefined) {

                    unitCostInput.value = cost;

                    calculateCost();
                }

            }
        );

    }


    if (quantityInput) {

        quantityInput.addEventListener(
            "input",
            calculateCost
        );

    }


    if (unitCostInput) {

        unitCostInput.addEventListener(
            "input",
            calculateCost
        );

    }

});