<?php
include "includes/db.php";
include "includes/header.php";
include "includes/navigation.php";
?>

<div class="container">

    <div class="row">
        <div class="col-md-8">

            <h1 class="page-header">
                Products
                <br><small>Browse our items</small>
            </h1>

            <?php
            $query = "SELECT * FROM products 
                      WHERE status='Active' AND is_deleted=0 
                      ORDER BY product_id DESC";
            $select_products = mysqli_query($connection, $query);

            if (!$select_products) {
                die("QUERY FAILED: " . mysqli_error($connection));
            }

            if (mysqli_num_rows($select_products) == 0) {
                echo "<p class='alert alert-warning'>No products available.</p>";
            }

            while ($product = mysqli_fetch_assoc($select_products)) {

                $product_id  = (int)$product['product_id'];
                $name        = htmlspecialchars($product['name']);
                $description = substr(strip_tags($product['description']), 0, 150) . "...";
                $price       = number_format($product['price'], 2);
                $image       = $product['image'];
                $created_at  = date("F j, Y", strtotime($product['created_at']));
            ?>

        <div class="well" style="padding:15px; margin-bottom:15px; font-size:14px;">
            
            <h4 style="margin-top:0;"><?php echo $name; ?></h4>

            <p style="margin:0 0 5px 0; font-size:12px; color:#777;">
                <span class="glyphicon glyphicon-time"></span>
                Added on <?php echo $created_at; ?>
            </p>

            <hr style="margin:5px 0;">

            <?php if (!empty($image) && file_exists("images/$image")): ?>
                <img class="img-responsive" 
                    src="images/<?php echo $image; ?>" 
                    alt="<?php echo $name; ?>" 
                    style="max-height:150px; width:auto; margin-bottom:5px;">
            <?php endif; ?>

            <p style="margin:5px 0;"><?php echo $description; ?></p>

            <h5 class="text-success" style="margin:5px 0;">$<?php echo $price; ?></h5>

            <button class="btn btn-success btn-xs add-to-cart-btn" 
                    data-id="<?php echo $product_id; ?>">
                <span class="glyphicon glyphicon-shopping-cart"></span> Add to Cart
            </button>

        </div>


            <?php } ?>

        </div>

        <?php include "includes/sidebar.php"; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>

<script>
document.querySelectorAll('.add-to-cart-btn').forEach(button => {
    button.addEventListener('click', () => {
        const productId = button.getAttribute('data-id');
        let qty = prompt("Enter quantity:", "1");
        if (qty !== null && qty !== "" && parseInt(qty) > 0) {
            qty = parseInt(qty);

            const form = document.createElement('form');
            form.method = 'post';
            form.action = 'cart.php?add=' + productId;

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'qty';
            input.value = qty;
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }
    });
});
</script>
