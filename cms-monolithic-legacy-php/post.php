<?php
include "includes/db.php";
include "includes/header.php";
include "includes/navigation.php";

$errMessage = false;

// Add new product comment/review
if (isset($_POST["create_comment"])) {
    $the_product_id = $_GET["p_id"];
    $comment_author = $_POST['comment_author'];
    $comment_email = $_POST['comment_email'];
    $comment_content = $_POST['comment_content'];

    if (!empty($comment_author) && !empty($comment_email) && !empty($comment_content)) {
        $query = "INSERT INTO comments (comment_post_id, comment_author, comment_email, comment_content, comment_status, comment_date) ";
        $query .= "VALUES('{$the_product_id}', '{$comment_author}', '{$comment_email}', '{$comment_content}', 'unapproved', now())";

        $create_comment_query = mysqli_query($connection, $query);
        if (!$create_comment_query) {
            die('QUERY FAILED: ' . mysqli_error($connection));
        }

        // Optional: Increment a comment count field in products table
        $query = "UPDATE products SET comment_count = comment_count + 1 WHERE product_id=$the_product_id";
        $update_comment_count = mysqli_query($connection, $query);
        if (!$update_comment_count) {
            die('QUERY FAILED: ' . mysqli_error($connection));
        }

        $errMessage = false;
    } else {
        $errMessage = true;
    }
}
?>

<div class="container">
    <?php
    if (isset($_GET['p_id'])) {
        $the_product_id = $_GET['p_id'];
        $query = "SELECT * FROM products WHERE product_id=$the_product_id AND is_deleted=0";
        $select_product_query = mysqli_query($connection, $query);
        if (!$select_product_query) {
            die("QUERY FAILED: " . mysqli_error($connection));
        }

        if (mysqli_num_rows($select_product_query) == 0) {
            echo "<p class='alert alert-warning'>Product not found.</p>";
        } else {
            $product = mysqli_fetch_assoc($select_product_query);

            $name = htmlspecialchars($product['name']);
            $description = htmlspecialchars($product['description']);
            $price = number_format($product['price'], 2);
            $stock = $product['stock_quantity'];
            $status = $product['status'];
            $created_at = date("F j, Y", strtotime($product['created_at']));
            $image = $product['image'];
    ?>

    <div class="row">
        <!-- Product Content Column -->
        <div class="col-md-8">
            <h1><?php echo $name; ?></h1>

            <?php if (!empty($image) && file_exists("images/$image")): ?>
                <img class="img-responsive" src="images/<?php echo $image; ?>" alt="<?php echo $name; ?>" style="max-width:500px; margin-bottom:15px;">
            <?php endif; ?>

            <p><?php echo nl2br($description); ?></p>
            <p><strong>Price:</strong> $<?php echo $price; ?></p>
            <p><strong>Stock:</strong> <?php echo $stock; ?></p>
            <p><small>Added on: <?php echo $created_at; ?></small></p>
            <hr>

            <!-- Comments Form -->
            <div class="well">
                <?php if ($errMessage === true) {
                    echo "<p class='alert alert-danger'>All fields are required.</p>";
                } ?>
                <h4>Leave a Review:</h4>
                <form role="form" action="" method="POST">
                    <div class="form-group">
                        <label for="comment_author">Name</label>
                        <input class="form-control" type="text" name="comment_author">
                    </div>
                    <div class="form-group">
                        <label for="comment_email">Email</label>
                        <input class="form-control" type="email" name="comment_email">
                    </div>
                    <div class="form-group">
                        <label for="comment_content">Your Review</label>
                        <textarea class="form-control" rows="3" name="comment_content"></textarea>
                    </div>
                    <button type="submit" name="create_comment" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <hr>

            <!-- Display Comments -->
            <?php
            $query = "SELECT * FROM comments WHERE comment_post_id=$the_product_id AND comment_status='Approved' ORDER BY comment_id DESC";
            $fetch_comments = mysqli_query($connection, $query);
            while ($comment = mysqli_fetch_assoc($fetch_comments)) {
            ?>
                <div class="media">
                    <a class="pull-left" href="#">
                        <img class="media-object" src="http://placehold.it/64x64" alt="">
                    </a>
                    <div class="media-body">
                        <h4 class="media-heading"><?php echo htmlspecialchars($comment['comment_author']); ?>
                            <small><?php echo date("F j, Y", strtotime($comment['comment_date'])); ?></small>
                        </h4>
                        <?php echo nl2br(htmlspecialchars($comment['comment_content'])); ?>
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- Sidebar Widgets Column -->
        <?php include "includes/sidebar.php"; ?>
    </div>

<?php
        }
    }
?>
</div>

<?php include "includes/footer.php"; ?>
