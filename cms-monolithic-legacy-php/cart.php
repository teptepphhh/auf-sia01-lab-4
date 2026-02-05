<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "includes/db.php";
include "includes/header.php";
include "includes/navigation.php";

if (isset($_GET['add'])) {
    $product_id = (int)$_GET['add'];
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
    if ($qty < 1) $qty = 1;

    $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $qty;

    header("Location: products.php");
    exit();
}

if (isset($_GET['increase'])) {
    $_SESSION['cart'][(int)$_GET['increase']]++;
    header("Location: cart.php");
    exit();
}

if (isset($_GET['decrease'])) {
    $pid = (int)$_GET['decrease'];
    $_SESSION['cart'][$pid]--;
    if ($_SESSION['cart'][$pid] < 1) unset($_SESSION['cart'][$pid]);
    header("Location: cart.php");
    exit();
}

if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][(int)$_GET['remove']]);
    header("Location: cart.php");
    exit();
}

if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit();
}

if (isset($_POST['checkout'])) {
    if (empty($_POST['selected_items'])) {
        $orderMessage = "Please select at least one item to check out.";
    } else {

        if (!empty($_SESSION['user_id'])) {
            $user_id = (int)$_SESSION['user_id'];

            $userQuery = mysqli_query($connection, "SELECT user_firstname, user_lastname, user_email FROM users WHERE user_id = $user_id AND role = 'customer'");
            $user = mysqli_fetch_assoc($userQuery);

            $customer_name  = $user ? trim($user['user_firstname'] . ' ' . $user['user_lastname']) : "Guest Customer";
            $customer_email = $user ? trim($user['user_email']) : "guest@example.com";
        } else {
            $user_id = NULL;
            $customer_name  = trim($_POST['customer_name'] ?? "Guest Customer");
            $customer_email = trim($_POST['customer_email'] ?? "guest@example.com");
        }

        $total_amount = 0;

        foreach ($_POST['selected_items'] as $pid) {
            $pid = (int)$pid;
            if (!isset($_SESSION['cart'][$pid])) continue;

            $q = mysqli_query($connection,
                "SELECT price FROM products 
                 WHERE product_id=$pid AND status='Active' AND is_deleted=0"
            );
            $p = mysqli_fetch_assoc($q);

            if ($p) {
                $total_amount += $p['price'] * $_SESSION['cart'][$pid];
            }
        }

        mysqli_query($connection, "
            INSERT INTO orders (user_id, total_amount, status)
            VALUES (" . ($user_id ? $user_id : "NULL") . ", $total_amount, 'pending')
        ");

        $order_id = mysqli_insert_id($connection);

        foreach ($_POST['selected_items'] as $pid) {
            $pid = (int)$pid;
            $qty = $_SESSION['cart'][$pid];

            $q = mysqli_query($connection,
                "SELECT price FROM products WHERE product_id=$pid"
            );
            $p = mysqli_fetch_assoc($q);
            if (!$p) continue;

            $price = $p['price'];

            mysqli_query($connection, "
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES ($order_id, $pid, $qty, $price)
            ");

            unset($_SESSION['cart'][$pid]);
        }

        $_SESSION['order_id'] = $order_id;
        $_SESSION['customer_name']  = $customer_name;
        $_SESSION['customer_email'] = $customer_email;

        header("Location: order-success.php");
        exit();
    }
}
?>

<div class="container">
    <h1 class="page-header">Your Cart</h1>

    <?php if (!empty($orderMessage)): ?>
        <p class="alert alert-warning"><?php echo $orderMessage; ?></p>
    <?php endif; ?>

    <?php if (empty($_SESSION['cart'])): ?>
        <p class="alert alert-info">Your cart is empty.</p>
        <a href="products.php" class="btn btn-primary">Continue Shopping</a>
    <?php else: ?>

    <form method="post" action="cart.php">

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center"><input type="checkbox" id="selectAll"></th>
                    <th>Product</th>
                    <th width="120">Price</th>
                    <th width="160" class="text-center">Quantity</th>
                    <th width="120">Subtotal</th>
                    <th width="90">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                foreach ($_SESSION['cart'] as $product_id => $qty):
                    $q = mysqli_query($connection,
                        "SELECT name, price FROM products 
                         WHERE product_id=$product_id AND status='Active' AND is_deleted=0"
                    );
                    $p = mysqli_fetch_assoc($q);
                    if (!$p) continue;

                    $subtotal = $p['price'] * $qty;
                    $total += $subtotal;
                ?>
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="selected_items[]" value="<?php echo $product_id; ?>" checked>
                    </td>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td>$<?php echo number_format($p['price'], 2); ?></td>
                    <td class="text-center">
                        <a href="cart.php?decrease=<?php echo $product_id; ?>" class="btn btn-xs btn-default">−</a>
                        <strong style="margin:0 10px;"><?php echo $qty; ?></strong>
                        <a href="cart.php?increase=<?php echo $product_id; ?>" class="btn btn-xs btn-default">+</a>
                    </td>
                    <td>$<?php echo number_format($subtotal, 2); ?></td>
                    <td>
                        <a href="cart.php?remove=<?php echo $product_id; ?>" class="btn btn-xs btn-danger">Remove</a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <tr>
                    <th colspan="4" class="text-right">Total</th>
                    <th colspan="2">$<?php echo number_format($total, 2); ?></th>
                </tr>
            </tbody>
        </table>

        <?php if (empty($_SESSION['user_id'])): ?>
        <div class="form-group">
            <label for="customer_name">Your Name:</label>
            <input type="text" name="customer_name" id="customer_name" class="form-control"
                   value="<?php echo htmlspecialchars($_SESSION['customer_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="customer_email">Your Email:</label>
            <input type="email" name="customer_email" id="customer_email" class="form-control"
                   value="<?php echo htmlspecialchars($_SESSION['customer_email'] ?? ''); ?>" required>
        </div>
        <?php endif; ?>

        <button type="submit" name="checkout" class="btn btn-success">
            <span class="glyphicon glyphicon-ok"></span> Check Out
        </button>

        <a href="products.php" class="btn btn-primary">Continue Shopping</a>
        <a href="cart.php?clear=1" class="btn btn-warning pull-right">Clear Cart</a>

    </form>

    <?php endif; ?>
</div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('input[name="selected_items[]"]').forEach(cb => {
        cb.checked = this.checked;
    });
});
</script>

<?php include "includes/footer.php"; ?>
