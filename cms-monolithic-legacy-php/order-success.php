<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "includes/db.php";
include "includes/header.php";
include "includes/navigation.php";

if (empty($_SESSION['order_id'])) {
    header("Location: cart.php");
    exit();
}

$order_id = (int) $_SESSION['order_id'];

$orderQuery = mysqli_query($connection, "
    SELECT * FROM orders WHERE order_id = $order_id
");
$order = mysqli_fetch_assoc($orderQuery);

if (!$order) {
    header("Location: cart.php");
    exit();
}

if (!empty($order['user_id'])) {
    $user_id = (int)$order['user_id'];
    $userQuery = mysqli_query($connection, "SELECT user_firstname, user_lastname, user_email FROM users WHERE user_id = $user_id AND role='customer'");
    $user = mysqli_fetch_assoc($userQuery);
    $customerName  = $user ? trim($user['user_firstname'] . ' ' . $user['user_lastname']) : "Guest Customer";
    $customerEmail = $user ? $user['user_email'] : "guest@example.com";
} else {
    $customerName  = $_SESSION['customer_name'] ?? "Guest Customer";
    $customerEmail = $_SESSION['customer_email'] ?? "guest@example.com";
}

$orderDate = date("F j, Y, g:i a", strtotime($order['order_date']));

$itemsQuery = mysqli_query($connection, "
    SELECT oi.quantity, oi.price, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = $order_id
");

$grandTotal = 0;
$orderItems = [];
while ($item = mysqli_fetch_assoc($itemsQuery)) {
    $subtotal = $item['price'] * $item['quantity'];
    $grandTotal += $subtotal;
    $orderItems[] = [
        'name'     => $item['name'],
        'price'    => $item['price'],
        'quantity' => $item['quantity'],
        'subtotal' => $subtotal
    ];
}

require 'vendor/autoload.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth   = true;
    $mail->Username   = '7c42216aebfe43';
    $mail->Password   = 'b014bf226ee100';
    $mail->Port       = 2525;

    $mail->setFrom('no-reply@yourshop.com', 'Your Shop');
    $mail->addAddress($customerEmail, $customerName);

    $mail->isHTML(true);
    $mail->Subject = "Order Confirmation #$order_id";

    $body = "<h2>Thank you for your order, ".htmlspecialchars($customerName)."!</h2>";
    $body .= "<p><strong>Order ID:</strong> #$order_id<br>";
    $body .= "<strong>Order Date:</strong> $orderDate<br>";
    $body .= "<strong>Total Amount:</strong> $".number_format($grandTotal,2)."</p>";

    $body .= "<h3>Ordered Products:</h3>";
    $body .= "<table border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>";
    foreach ($orderItems as $oi) {
        $body .= "<tr>
                    <td>".htmlspecialchars($oi['name'])."</td>
                    <td>$".number_format($oi['price'],2)."</td>
                    <td>".$oi['quantity']."</td>
                    <td>$".number_format($oi['subtotal'],2)."</td>
                  </tr>";
    }
    $body .= "<tr>
                <th colspan='3'>Grand Total</th>
                <th>$".number_format($grandTotal,2)."</th>
              </tr>";
    $body .= "</table>";

    $mail->Body = $body;
    $mail->send();
    $emailMessage = "<p class='alert alert-success'>A confirmation email has been sent to $customerEmail.</p>";
} catch (Exception $e) {
    $emailMessage = "<p class='alert alert-danger'>Could not send email. Mailer Error: {$mail->ErrorInfo}</p>";
}
?>

<div class="container">

<h1 class="page-header text-success">Order Confirmation</h1>

<?php echo $emailMessage; ?>

<div class="panel panel-default">
    <div class="panel-heading"><strong>Customer Information</strong></div>
    <div class="panel-body">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($customerName); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($customerEmail); ?></p>
    </div>
</div>

<div class="panel panel-info">
    <div class="panel-heading"><strong>Order Information</strong></div>
    <div class="panel-body">
        <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
        <p><strong>Order Date:</strong> <?php echo $orderDate; ?></p>
        <p><strong>Total Amount:</strong> $<?php echo number_format($grandTotal, 2); ?></p>
    </div>
</div>

<div class="panel panel-success">
    <div class="panel-heading"><strong>Ordered Products</strong></div>

<table class="table table-bordered table-striped">
<thead>
<tr>
    <th>Product</th>
    <th width="120">Price</th>
    <th width="120">Quantity</th>
    <th width="120">Subtotal</th>
</tr>
</thead>
<tbody>
<?php foreach ($orderItems as $oi): ?>
<tr>
    <td><?php echo htmlspecialchars($oi['name']); ?></td>
    <td>$<?php echo number_format($oi['price'],2); ?></td>
    <td><?php echo $oi['quantity']; ?></td>
    <td>$<?php echo number_format($oi['subtotal'],2); ?></td>
</tr>
<?php endforeach; ?>
<tr>
    <th colspan="3" class="text-right">Grand Total</th>
    <th>$<?php echo number_format($grandTotal,2); ?></th>
</tr>
</tbody>
</table>
</div>

<a href="products.php" class="btn btn-primary">Continue Shopping</a>
<a href="cart.php" class="btn btn-default">View Cart</a>

</div>

<?php
unset($_SESSION['order_id'], $_SESSION['customer_name'], $_SESSION['customer_email']);

include "includes/footer.php";
?>
