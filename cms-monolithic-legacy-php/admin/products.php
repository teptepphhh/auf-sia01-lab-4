<?php
include "includes/header.php";
include "includes/navigation.php";
?>

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">
                    Products
                    <small>Manage Products</small>
                </h1>
            </div>

            <div class="col-xs-12">
                <?php
                $source = $_GET['source'] ?? '';

                switch ($source) {
                    case 'add_product':
                        include "./includes/add_product.php";
                        break;

                    case 'edit_product':
                        include "./includes/edit_product.php";
                        break;

                    default:
                        include "./includes/view_all_products.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
