<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type');

// Connect to the MySQL database
$host = '127.0.0.1';
$dbname = 'recipes'; 
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle database connection error
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Database connection error']);
    exit();
}

// Handle API requests
$requestMethod = $_SERVER['REQUEST_METHOD'];
$resourceId = isset($_GET['id']) ? $_GET['id'] : null;

if ($requestMethod === 'GET') {
    // Fetch all recipes or a specific recipe
    if ($resourceId !== null) {
        // Get a specific recipe by ID
        $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = :id");
        $stmt->bindParam(':id', $resourceId);
        $stmt->execute();
        $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recipe) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['error' => 'Recipe not found']);
            exit();
        }

        header('Content-Type: application/json');
        echo json_encode($recipe);
    } else {
        // Get all recipes
        $stmt = $pdo->query("SELECT * FROM recipes");
        $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($recipes);
    }
} elseif ($requestMethod === 'POST') {
    // Create a new recipe
    $requestData = json_decode(file_get_contents('php://input'), true);

    // Validate the request data (e.g., check required fields)
    if (
        empty($requestData['name']) ||
        empty($requestData['image']) ||
        empty($requestData['description']) ||
        empty($requestData['ingredients']) ||
        empty($requestData['instructions'])
    ) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Incomplete recipe data']);
        exit();
    }

    // Insert the new recipe into the database
    $stmt = $pdo->prepare("INSERT INTO recipes (name, image, description, ingredients, instructions) VALUES (:name, :image, :description, :ingredients, :instructions)");
    $stmt->bindParam(':name', $requestData['name']);
    $stmt->bindParam(':image', $requestData['image']);
    $stmt->bindParam(':description', $requestData['description']);
    $stmt->bindParam(':ingredients', $requestData['ingredients']);
    $stmt->bindParam(':instructions', $requestData['instructions']);
    $stmt->execute();

    // Return the newly created recipe with its generated ID
    $recipeId = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = :id");
    $stmt->bindParam(':id', $recipeId);
    $stmt->execute();
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    http_response_code(201);
    echo json_encode($recipe);
} elseif ($requestMethod === 'PUT') {
    // Update an existing recipe
    if ($resourceId === null) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Resource ID not provided']);
        exit();
    }

    $requestData = json_decode(file_get_contents('php://input'), true);

    // Validate the request data (e.g., check required fields)
    if (
        empty($requestData['name']) ||
        empty($requestData['image']) ||
        empty($requestData['description']) ||
        empty($requestData['ingredients']) ||
        empty($requestData['instructions'])
    ) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Incomplete recipe data']);
        exit();
    }

    // Update the recipe in the database
    $stmt = $pdo->prepare("UPDATE recipes SET name = :name, image = :image, description = :description, ingredients = :ingredients, instructions = :instructions WHERE id = :id");
    $stmt->bindParam(':name', $requestData['name']);
    $stmt->bindParam(':image', $requestData['image']);
    $stmt->bindParam(':description', $requestData['description']);
    $stmt->bindParam(':ingredients', $requestData['ingredients']);
    $stmt->bindParam(':instructions', $requestData['instructions']);
    $stmt->bindParam(':id', $resourceId);
    $stmt->execute();

    // Return the updated recipe
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = :id");
    $stmt->bindParam(':id', $resourceId);
    $stmt->execute();
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($recipe);
} elseif ($requestMethod === 'DELETE') {
    // Delete an existing recipe
    if ($resourceId === null) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Resource ID not provided']);
        exit();
    }

    // Delete the recipe from the database
    $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = :id");
    $stmt->bindParam(':id', $resourceId);
    $stmt->execute();

    header('Content-Type: application/json');
    http_response_code(204);
} else {
    // Invalid request method
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}
?>
