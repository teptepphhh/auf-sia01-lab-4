<?php
if (isset($_POST['update_product'], $_GET['p_id'])) {
    $the_product_id = $_GET['p_id'];

    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $price = mysqli_real_escape_string($connection, $_POST['price']);
    $stock_quantity = mysqli_real_escape_string($connection, $_POST['stock_quantity']);
    $status = mysqli_real_escape_string($connection, $_POST['status']);
    $updated_at = date('Y-m-d H:i:s');

    $product_image = $_FILES['product_image']['name'];
    $product_image_temp = $_FILES['product_image']['tmp_name'];

    if (!empty($product_image)) {
        move_uploaded_file($product_image_temp, "../images/$product_image");
        $image_query = ", image='$product_image'";
    } else {
        $image_query = ""; 
    }

    $query = "UPDATE products SET 
                name='$name', 
                description='$description', 
                price='$price', 
                stock_quantity='$stock_quantity', 
                status='$status' 
                $image_query
                WHERE product_id=$the_product_id";

    $update_product_query = mysqli_query($connection, $query);
    if (!$update_product_query) {
        die("Query Failed: " . mysqli_error($connection));
    }

    echo "<p class='alert alert-success'>Product updated successfully. <a href='products.php?source=edit_product&p_id=$the_product_id'>Edit Product</a></p>";
}

if (isset($_GET['p_id'])) {
    $the_product_id = $_GET['p_id'];
    $query = "SELECT * FROM products WHERE product_id=$the_product_id";
    $fetch_data = mysqli_query($connection, $query);
    if (!$fetch_data) {
        die("Query Failed: " . mysqli_error($connection));
    }
    $product = mysqli_fetch_assoc($fetch_data);

    $name = $product['name'];
    $description = $product['description'];
    $price = $product['price'];
    $stock_quantity = $product['stock_quantity'];
    $status = $product['status'];
    $current_image = $product['image'];
?>

<form action="" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="name">Product Name *</label>
        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
    </div>

    <div class="form-group">
        <label for="price">Price *</label>
        <input type="number" step="0.01" class="form-control" name="price" value="<?php echo $price; ?>" required>
    </div>

    <div class="form-group">
        <label for="stock_quantity">Stock Quantity *</label>
        <input type="number" class="form-control" name="stock_quantity" value="<?php echo $stock_quantity; ?>" required>
    </div>

    <div class="form-group">
        <label for="status">Status</label>
        <select name="status" class="form-control">
            <option value="<?php echo $status; ?>"><?php echo $status; ?></option>
            <?php if ($status === "Active") { ?>
                <option value="Inactive">Inactive</option>
            <?php } else { ?>
                <option value="Active">Active</option>
            <?php } ?>
        </select>
    </div>

    <div class="form-group">
        <label for="product_image">Product Image</label>
        <?php if (!empty($current_image)) { ?>
            <br>
            <img src="../images/<?php echo $current_image; ?>" width="100" alt="Current Product Image">
            <br><br>
        <?php } ?>
        <input type="file" name="product_image">
    </div>

    <div class="form-group">
        <input type="submit" class="btn btn-primary" name="update_product" value="Update Product">
    </div>
</form>

<?php } ?>
