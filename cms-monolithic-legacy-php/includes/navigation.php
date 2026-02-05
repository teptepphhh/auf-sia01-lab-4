<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Cart item count (total quantity) */
$cart_count = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += (int)$qty;
    }
}
?>

<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">

        <!-- Brand and toggle -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php">CMS Blog</a>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

            <!-- LEFT NAV -->
            <ul class="nav navbar-nav">
                <?php
                $query = "SELECT * FROM categories";
                $fetch_data = mysqli_query($connection, $query);
                while ($row = mysqli_fetch_assoc($fetch_data)) {
                    $cat_title = htmlspecialchars($row['cat_title']);
                    $cat_id = (int)$row['cat_id'];
                    echo "<li><a href='category.php?category=$cat_id'>$cat_title</a></li>";
                }
                ?>

                <li><a href="products.php">Products</a></li>
                <li><a href="admin">Admin</a></li>
                <li><a href="registration.php">Registration</a></li>

                <?php
                if (isset($_SESSION['role']) && isset($_GET['p_id'])) {
                    $the_post_id = (int)$_GET['p_id'];
                    echo "<li><a href='admin/posts.php?source=edit_post&p_id=$the_post_id'>Edit Post</a></li>";
                }
                ?>
            </ul>

            <!-- RIGHT NAV -->
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="cart.php">
                        <span class="glyphicon glyphicon-shopping-cart"></span>

                        <?php if ($cart_count > 0): ?>
                            <span class="badge" style="background:#d9534f;">
                                <?php echo $cart_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>

        </div>
    </div>
</nav>
