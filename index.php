<?
define("BASE_PATH", realpath('.') . DIRECTORY_SEPARATOR);
include "function.php";

$item = isset($_GET['item']) ? intval($_GET['item']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>WoW Auction Search</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="container">
    <nav class="navbar navbar-upbootstrap navbar-fixed-top" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <a class="navbar-brand" href="/">
                    Logo
                </a>
            </div>
            <form class="navbar-form navbar-right col-xs-4" method="get" role="search">
                <div class="form-group">
                    <input id="search-input" type="text" class="form-control input-sm" name="item"
                           placeholder="Search Item...">
                </div>
            </form>
        </div>
    </nav>
    <div class="row">
        <? checkForAuctionUpdate() ?>
        <div class="row">
            <div class="col-md-6 ">
                <? if ($item > 0) { ?>
                    <? $itemData = getItemData($item); ?>
                    <div class="well">
                        <img src="http://wow.zamimg.com/images/wow/icons/large/<?= $itemData['icon'] ?>.jpg"
                             align="left" style="padding-right: 6px">
                        <h3 style="margin-top: 0"><?= $itemData['name'] ?></h3>
                        <span><?= $itemData['description'] ?></span>
                    </div>
                <? } ?>
                <div class="well">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h2>Adet</h2>
                            <span id="totalQuantity">0</span>
                        </div>
                        <div class="col-md-4 text-center">
                            <h2>Toplam</h2>
                            <span id="totalPrice">0</span>
                        </div>
                        <div class="col-md-4 text-center">
                            <h2>Ortalama</h2>
                            <span id="avgPrice">0</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 ">
                <h1>Active Auctions</h1>

                <? if ($item > 0) { ?>
                    <? $reults = findItemAuctions($item); ?>
                    <? if (count($reults) > 0) { ?>
                        <table class="table table-hover" id="auctionTable">
                            <thead>
                            <tr>
                                <th class="col-md-1"></th>
                                <th class="col-md-6">Fiyat</th>
                                <th class="col-md-5">Adet</th>
                            </tr>
                            </thead>
                            <tbody>
                            <? foreach ($reults AS $price => $quantity) { ?>
                                <tr>
                                    <td><input type="checkbox" name="selected[]" value="<?= $price ?>"
                                               data-quantity="<?= $quantity ?>"></td>
                                    <td><?= formatPrice($price) ?></td>
                                    <td><?= $quantity ?></td>
                                </tr>
                            <? } ?>
                            </tbody>
                        </table>
                    <? } ?>
                <? } ?>
            </div>

        </div>
    </div>
</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-typeahead.min.js"></script>
<script>
    function formatMoney(money) {
        var moneyText, bronze, silver, gold;
        money = money * 1;
        if (money < 1) return "0";
        moneyText = money + "";
        bronze = moneyText.substr(-2, 2);
        if (money > 99) {
            silver = moneyText.substr(-4, 2);
            if (money > 9999) {
                gold = moneyText.substr(0, moneyText.length - 4);
            } else {
                gold = "0";
            }
        } else {
            silver = "0";
            gold = "0";
        }
        return gold + "g " + silver + "s " + bronze + "b";

    }

    var totalPrice = 0;
    var $totalPrice = $("#totalPrice");
    var avgPrice = 0;
    var $avgPrice = $("#avgPrice");
    var totalQuantity = 0;
    var $totalQuantity = $("#totalQuantity");
    $(function () {
        $('#search-input').typeahead({
            ajax: '/items.php'
        });

        $("#auctionTable").on("click", "tbody tr", function (event) {
            if (event.target.type !== 'checkbox') {
                $(this).find("input").trigger("click");
            }
        }).on("click", "input[type=checkbox]", function (e) {
            totalPrice = 0;
            totalQuantity = 0;
            $.each($("#auctionTable").find("input[type=checkbox]:checked"), function (index, value) {
                quantity = $(value).data("quantity") * 1;
                totalQuantity += quantity;
                totalPrice += $(value).val() * quantity;
                console.log("totalPrice" + totalPrice);
            });

            $totalPrice.html(formatMoney(totalPrice));
            $totalQuantity.html(totalQuantity);
            $avgPrice.html(formatMoney(Math.ceil(totalPrice / totalQuantity)));
        });
    });
</script>
</body>
</html>
