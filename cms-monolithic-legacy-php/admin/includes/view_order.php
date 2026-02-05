<?php
if (!isset($_GET['order_id'])) {
    echo "<p class='alert alert-warning'>No order selected.</p>";
    exit;
}

$order_id = (int)$_GET['order_id'];

$orderQuery = "
    SELECT o.*, u.user_firstname, u.user_lastname, u.user_email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = $order_id
";
$orderResult = mysqli_query($connection, $orderQuery);
$order = mysqli_fetch_assoc($orderResult);

if (!$order) {
    echo "<p class='alert alert-danger'>Order not found.</p>";
    exit;
}

$customerName  = $order['user_id'] ? 
                 $order['user_firstname'] . ' ' . $order['user_lastname'] : ($_SESSION['customer_name'] ?? "Guest");
$customerEmail = $order['user_id'] ? $order['user_email'] : ($_SESSION['customer_email'] ?? "N/A");
?>

<div class="container">
<h1 class="page-header text-success">Order Details</h1>

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
        <p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
        <p><strong>Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($order['order_date'])); ?></p>
        <p><strong>Status:</strong> 
            <span class="label label-<?php echo $order['status'] === 'completed' ? 'success' : 'warning'; ?>">
                <?php echo ucfirst($order['status']); ?>
            </span>
        </p>
        <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
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
            <?php
            $itemsQuery = "
                SELECT oi.*, p.name 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.product_id
                WHERE oi.order_id = $order_id
            ";
            $itemsResult = mysqli_query($connection, $itemsQuery);
            $grandTotal = 0;

            while ($item = mysqli_fetch_assoc($itemsResult)) {
                $subtotal = $item['price'] * $item['quantity'];
                $grandTotal += $subtotal;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($subtotal, 2); ?></td>
                </tr>
            <?php } ?>
            <tr>
                <th colspan="3" class="text-right">Grand Total</th>
                <th>$<?php echo number_format($grandTotal, 2); ?></th>
            </tr>
        </tbody>
    </table>
</div>

<a href="orders.php" class="btn btn-default">Back to Orders</a>
</div>
