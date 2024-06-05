<?php
require "vendor/autoload.php";

use App\Products2;
use App\Authorize;

$user = new Authorize("users.json");
$userName = $user->authorize();

$product = new Products2("products.json", $userName);
$product->updateJSON();
while (true) {
    echo "Enter choice: 
[1] Create product
[2] Edit product
[3] Delete product
[4] Display products
[5] Check logs
[6] Create report
[Any key] Exit
______________________" . PHP_EOL;
    $choice = (int)readline();
    switch ($choice) {
        case 1:
            $product->create();
            break;
        case 2:
            $product->edit();
            break;
        case 3:
            $product->delete();
            break;
        case 4:
            $product->display();
            break;
        case 5:
            $product->logs();
            break;
        case 6:
            $product->report();
            break;
        default:
            exit("Goodbye");
    }
}