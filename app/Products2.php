<?php

namespace app;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class Products2
{
    private string $file;
    private string $user;

    public function __construct(string $file, string $user)
    {
        $this->file = $file;
        $this->user = $user;
        if (!file_exists($this->file)) {
            file_put_contents($this->file, json_encode(['products' => []], JSON_PRETTY_PRINT));
        }
    }

    private function loadProducts(): ?array
    {
        return json_decode(file_get_contents($this->file), true);
    }

    private function saveProducts(array $data): void
    {
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function create(): void
    {
        $data = $this->loadProducts();
        $id = Uuid::uuid4()->toString();
        $name = readline("Enter product name: ");
        $amount = readline("Enter amount: ");
        if ($amount < 0) {
            echo "Incorrect amount" . PHP_EOL;
            $amount = 0;
        }
        $price = readline("Enter price: ");
        if ($price < 0) {
            echo "Incorrect price";
            $price = 0;
        }
        $expiration = readline("Enter expiration date (YYYY-mm-dd/null): ");
        $created = Carbon::now()->toDateTimeString();
        $lastUpdate = Carbon::now()->toDateTimeString();
        $data['products'][] = [
            "id" => $id,
            "name" => $name,
            "amount" => $amount,
            "price" => $price,
            "expiration" => $expiration,
            "created" => $created,
            "updated" => $lastUpdate];
        $this->saveProducts($data);
        $this->logChanges('created', $id, ['name' => $name, 'amount' => $amount, 'price' => $price, 'expiration' => $expiration]);
    }

    private function logChanges(string $action, string $productID, array $changes = []): void
    {
        $logFile = "logs.json";
        if (!file_exists($logFile)) {
            file_put_contents($logFile, json_encode(['logs' => []], JSON_PRETTY_PRINT));
        }
        $logData = json_decode(file_get_contents($logFile), true);
        $logEntry = [
            "user" => $this->user,
            "product_id" => $productID,
            "action" => $action,
            "changes" => $changes,
            "time" => Carbon::now()->toDateTimeString()
        ];
        $logData["logs"][] = $logEntry;
        file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT));
    }

    public function edit(): void
    {
        $data = $this->loadProducts();
        if (empty($data["products"])) {
            echo "No products to edit " . PHP_EOL;
            return;
        }
        $edit = (int)readline("enter index of product to edit: ");
        if ($edit < 0 || $edit >= count($data["products"])) {
            echo "Invalid index" . PHP_EOL;
            return;
        }
        $product = &$data["products"][$edit];
        $changes = [];
        $changeName = strtolower(readline("Change product name (y/yes)? "));
        if ($changeName === "y" || $changeName === "yes") {
            $newName = readline("Enter new name ");
            if ($newName !== $product["name"]) {
                $changes["name"] = ["old" => $product["name"], "new" => $newName];
                $product["name"] = $newName;
                $product["updated"] = Carbon::now()->toDateTimeString();
            }
        }
        $changeAmount = strtolower(readline("Change product amount (y/yes)? "));
        if ($changeAmount === "y" || $changeAmount === "yes") {
            $newAmount = (int)readline("Enter new amount ");
            if ($newAmount !== $product["amount"]) {
                $changes["amount"] = ["old" => $product["amount"], "new" => $newAmount];
                $product["amount"] = $newAmount;
                $product["updated"] = Carbon::now()->toDateTimeString();
            }
        }
        $changePrice = strtolower(readline("Change price (y/yes)? "));
        if ($changePrice === "y" || $changePrice === "yes") {
            $newPrice = readline("Enter new price ");
            if ($newPrice !== $product["price"]) {
                $changes["price"] = ["old" => $product["price"], "new" => $newPrice];
                $product["price"] = $newPrice;
                $product["updated"] = carbon::now()->toDateTimeString();
            }
        }
        $changeExpiration = strtolower(readline("Change expiration date (y/yes)? "));
        if ($changeExpiration === "y" || $changeExpiration === "yes") {
            $newExpiration = readline("Enter new expiration date (yyyy-mm-dd/null) ");
            if ($newExpiration !== $product["expiration"]) {
                $changes["expiration"] = ["old" => $product["expiration"], "new" => $newExpiration];
                $product["expiration"] = $newExpiration ?: null;
                $product["updated"] = carbon::now()->toDateTimeString();
            }

            if (!empty($changes)) {
                $this->logChanges('edited', $edit, $changes);
            }
        }
        $this->saveProducts($data);
    }

    public function delete(): void
    {
        $data = $this->loadProducts();
        if (empty($data["products"])) {
            echo "No products to delete " . PHP_EOL;
            return;
        }
        $delete = (int)readline("Enter product index to delete ");
        if ($delete < 0 || $delete >= count($data["products"])) {
            echo "Invalid index" . PHP_EOL;
            return;
        }
        $deletedProduct = $data["products"][$delete];
        array_splice($data["products"], $delete, 1);
        $this->logChanges("deleted", $deletedProduct["id"], ["deleted_product" => $deletedProduct]);

        $this->saveProducts($data);
        echo "Product deleted." . PHP_EOL;
    }

    public function display(): void
    {
        $data = $this->loadProducts();
        if (empty($data["products"])) {
            echo "No products found " . PHP_EOL;
            return;
        }
        $output = new ConsoleOutput();
        $table = new Table($output);
        $table->setHeaders(["Index", "ID", "Name", "Amount", "Price", "Expiration", "Created", "Updated"]);
        foreach ($data["products"] as $index => $product) {
            $table->addRow([
                $index,
                $product["id"],
                $product["name"],
                $product["amount"],
                $product["price"],
                $product["expiration"],
                $product["created"],
                $product["updated"]
            ]);
        }
        $table->render();
    }

    public
    function logs(): void
    {
        $logFile = "logs.json";
        if (!file_exists($logFile)) {
            echo "No logs found" . PHP_EOL;
            return;
        }
        $logData = json_decode(file_get_contents($logFile), true);
        if (empty($logData["logs"])) {
            echo "No logs found" . PHP_EOL;
            return;
        }
        $output = new ConsoleOutput();
        $table = new Table($output);
        $table->setHeaders(["User", "ID", "Action", "Changes", "Time"]);
        foreach ($logData["logs"] as $log) {
            $changes = !empty($log["changes"]) ? json_encode($log["changes"]) : "N/A";
            $table->addRow([
                $log["user"],
                $log["product_id"],
                $log["action"],
                $changes,
                $log["time"]
            ]);
        }
        $table->render();
    }

    public
    function report(): void
    {
        $data = $this->loadProducts();
        $totalProducts = count($data["products"]);
        $totalValue = 0;
        $totalAmount = 0;
        foreach ($data["products"] as $product) {
            $totalValue += $product["price"] * $product["amount"];
            $totalAmount += $product["amount"];
        }
        echo "Total number of unique products: $totalProducts 
Total sum of products: $totalValue \nTotal amount of products: $totalAmount" . PHP_EOL;
    }

    public
    function updateJSON(): void
    {
        $data = $this->loadProducts();
        foreach ($data["products"] as &$product) {
            if (is_int($product["id"])) {
                $product["id"] = Uuid::uuid4()->toString();
            }
            if (!array_key_exists("price", $product)) {
                $product["price"] = 0;
            }
            if (!array_key_exists("expiration", $product)) {
                $product["expiration"] = null;
            }
        }
        $this->saveProducts($data);
    }
}