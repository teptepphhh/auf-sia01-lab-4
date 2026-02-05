<?php
$query = "
    SELECT o.order_id, o.user_id, o.total_amount, o.order_date, o.status,
           u.user_firstname, u.user_lastname, u.user_email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
";

$result = mysqli_query($connection, $query);
?>

<table class="table table-bordered table-striped">
<thead>
<tr>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Email</th>
    <th>Total</th>
    <th>Date</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($result) > 0): ?>
    <?php while($order = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo $order['order_id']; ?></td>
            <td>
                <?php 
                    echo $order['user_id'] 
                        ? htmlspecialchars($order['user_firstname'] . ' ' . $order['user_lastname']) 
                        : "Guest";
                ?>
            </td>
            <td>
                <?php 
                    echo $order['user_id'] 
                        ? htmlspecialchars($order['user_email']) 
                        : "N/A"; 
                ?>
            </td>
            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
            <td><?php echo date("F j, Y, g:i a", strtotime($order['order_date'])); ?></td>
            <td>
                <span class="label label-<?php 
                    echo $order['status'] === 'completed' ? 'success' : 'warning'; 
                ?>">
                    <?php echo ucfirst($order['status']); ?>
                </span>
            </td>
            <td>
                <a href="orders.php?source=view_order&order_id=<?php echo $order['order_id']; ?>" class="btn btn-xs btn-info">View</a>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="7" class="text-center">No orders found.</td>
    </tr>
<?php endif; ?>
</tbody>
</table>
