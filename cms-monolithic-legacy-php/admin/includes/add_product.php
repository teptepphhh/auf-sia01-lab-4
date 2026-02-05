<?php
if (isset($_POST['create_product'])) {
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $price = mysqli_real_escape_string($connection, $_POST['price']);
    $stock_quantity = mysqli_real_escape_string($connection, $_POST['stock_quantity']);
    $status = mysqli_real_escape_string($connection, $_POST['status']);
    $created_at = date('Y-m-d H:i:s');

    $product_image = $_FILES['product_image']['name'];
    $product_image_temp = $_FILES['product_image']['tmp_name'];

    if (empty($name) || empty($price) || empty($stock_quantity)) {
        echo "<p class='alert alert-danger'>Please fill in all required fields (Name, Price, Stock Quantity).</p>";
    } else {
        if (!empty($product_image)) {
            move_uploaded_file($product_image_temp, "../images/$product_image");
        } else {
            $product_image = null; 
        }

        $query = "INSERT INTO products (name, description, price, stock_quantity, status, created_at, image) ";
        $query .= "VALUES ('{$name}', '{$description}', '{$price}', '{$stock_quantity}', '{$status}', '{$created_at}', '{$product_image}')";
        $create_product_query = mysqli_query($connection, $query);

        if (!$create_product_query) {
            die("Query Failed: " . mysqli_error($connection));
        }

        header("Location: products.php");
        exit();
    }
}
?>

<form action="" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="name">Product Name *</label>
        <input type="text" class="form-control" name="name" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" class="form-control" rows="5"></textarea>
    </div>

    <div class="form-group">
        <label for="price">Price *</label>
        <input type="number" step="0.01" class="form-control" name="price" required>
    </div>

    <div class="form-group">
        <label for="stock_quantity">Stock Quantity *</label>
        <input type="number" class="form-control" name="stock_quantity" required>
    </div>

    <div class="form-group">
        <label for="status">Status</label>
        <select name="status" class="form-control">
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
    </div>

    <div class="form-group">
        <label for="product_image">Product Image</label>
        <input type="file" name="product_image">
    </div>

    <div class="form-group">
        <input type="submit" class="btn btn-primary" name="create_product" value="Add Product">
    </div>
</form>
