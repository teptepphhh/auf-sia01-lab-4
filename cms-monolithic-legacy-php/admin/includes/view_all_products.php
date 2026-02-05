<?php
if (isset($_GET["delete"])) {
    $product_id = (int)$_GET['delete'];
    $query = "UPDATE products SET is_deleted = 1 WHERE product_id = $product_id";
    $delete_query = mysqli_query($connection, $query);
    if (!$delete_query) {
        die("Query Failed: " . mysqli_error($connection));
    }
    header("Location: products.php");
    exit();
}

if (isset($_POST["apply"])) {
    if (!empty($_POST["checkBoxArray"])) {
        foreach ($_POST["checkBoxArray"] as $checkBoxValue) {
            $product_id = (int)$checkBoxValue;
            $bulk_option = $_POST['bulk_option'];

            switch ($bulk_option) {
                case 'Active':
                case 'Inactive':
                    $query = "UPDATE products SET status='$bulk_option' WHERE product_id=$product_id";
                    mysqli_query($connection, $query);
                    break;

                case 'Delete':
                    $query = "UPDATE products SET is_deleted = 1 WHERE product_id = $product_id";
                    mysqli_query($connection, $query);
                    break;

                default:
                    echo "<p class='alert alert-danger'>Please select a valid option.</p>";
                    break;
            }
        }
    } else {
        echo "<p class='alert alert-danger'>Please select at least one product.</p>";
    }
}
?>

<form action="" method="POST">
    <div class="row mb-3">
        <div class="col-sm-4">
            <select class="form-control" name="bulk_option">
                <option value="">Select Options</option>
                <option value="Active">Activate</option>
                <option value="Inactive">Deactivate</option>
                <option value="Delete">Delete</option>
            </select>
        </div>
        <div class="col-sm-4">
            <input type="submit" class="btn btn-success" name="apply" value="Apply">
            <a class="btn btn-primary" href="products.php?source=add_product">Add New Product</a>
        </div>
    </div>

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th><input type='checkbox' id='selectAllBoxes' onclick="selectAll(this)"></th>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT * FROM products WHERE is_deleted = 0 ORDER BY created_at DESC";
            $fetch_products = mysqli_query($connection, $query);

            while ($Row = mysqli_fetch_assoc($fetch_products)) {
                $product_id = $Row['product_id'];
                $product_image = $Row['image'];
            ?>
                <tr>
                    <td><input type='checkbox' name='checkBoxArray[]' value='<?php echo $product_id; ?>'></td>
                    <td><?php echo $product_id; ?></td>
                    <td>
                        <?php
                        if (!empty($product_image) && file_exists("../images/$product_image")) {
                            echo "<img src='../images/" . htmlspecialchars($product_image) . "' width='80' alt='" . htmlspecialchars($Row['name']) . "'>";
                        } else {
                            echo "No Image";
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($Row['name']); ?></td>
                    <td><?php echo htmlspecialchars($Row['description']); ?></td>
                    <td>$<?php echo number_format($Row['price'], 2); ?></td>
                    <td><?php echo (int)$Row['stock_quantity']; ?></td>
                    <td><?php echo htmlspecialchars($Row['status']); ?></td>
                    <td><?php echo $Row['created_at']; ?></td>
                    <td>
                        <a onClick="return confirm('Are you sure you want to delete this product?');"
                           href='products.php?delete=<?php echo $product_id; ?>'>Delete</a> |
                        <a href='products.php?source=edit_product&p_id=<?php echo $product_id; ?>'>Edit</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</form>

<script>
    function selectAll(source) {
        const checkboxes = document.getElementsByName('checkBoxArray[]');
        for (let i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = source.checked;
        }
    }
</script>
