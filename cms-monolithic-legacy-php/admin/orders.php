<?php
include "includes/header.php";
include "includes/navigation.php";
?>

<div id="page-wrapper">
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">
                    Orders
                    <small>Manage Orders</small>
                </h1>
            </div>

            <div class="col-xs-12">
                <?php
                $source = $_GET['source'] ?? '';

                switch ($source) {

                    case 'view_order':
                        include "./includes/view_order.php";
                        break;

                    default:
                        include "./includes/view_all_orders.php";
                        break;
                }
                ?>
            </div>
        </div>

    </div>
</div>

<?php include "includes/footer.php"; ?>
