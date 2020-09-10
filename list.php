<?php
include('config.inc');

try {
    // Establecimiento de conexión.
    $conn = new PDO("mysql:dbname=$dbname;host=$host", $username, $password);

    // Consultas auxiliares para los filtros.
    $categorias = $conn->query("SELECT name FROM category");

    // Consulta de los datos.
    $sql = "SELECT * FROM film_list";
    $constraints = [];


    if ($_GET['rating']) $constraints['rating'] = $_GET['rating'];
    if ($_GET['category']) $constraints['category'] = $_GET['category'];


    if (count($constraints) > 0) {
        $sql = $sql . ' WHERE ' . implode(
            ' AND ',
            array_map(
                function ($x) { return $x . ' = ?'; }, 
                array_keys($constraints)
            )
        );
    }

    $page = intval($_GET['_page'] ?? 1);
    $offset = 20 * ($page - 1);
    $sql = $sql . " LIMIT 20 OFFSET $offset"; 

    $statement = $conn->prepare($sql); 
    $statement->execute(array_values($constraints));
    $rows = $statement->fetchAll();


    $prevLink = ($page>1)? 'list.php?' . http_build_query(array_merge($_GET, ['_page' => $page-1])) : false;
    $nextLink = (count($rows) == 20)? 'list.php?' . http_build_query(array_merge($_GET, ['_page' => $page+1])) : false;

} catch (PDOException $pe) {
    die("Could not connect to the database $dbname :" . $pe->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Películas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
</head>
<body>
    <div class="container-fluid">
        <form action="list.php" method="get" class="form-inline my-3">

                    <label for="rating">Categoría</label>
                    <select name="category" id="rating" class="custom-select mx-2">
                        <option value></option>
                        <?php foreach($categorias as $categoria): ?>
                        <option value="<?= $categoria['name'] ?>" <?= $_GET['category']==$categoria['name']? 'selected' : '' ?>><?= $categoria['name'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="rating">Clasificación</label>
                    <select name="rating" id="rating" class="custom-select mx-2">
                        <option value></option>
                        <?php foreach(['G','PG','PG-13','R','NC-17'] as $rating): ?>
                        <option value="<?= $rating ?>" <?= $_GET['rating']==$rating? 'selected' : '' ?>><?= $rating ?></option>
                        <?php endforeach; ?>
                    </select>
                    
            <input type="submit" class="btn btn-primary" value="Buscar">
        
        </form>
    
        <code><?= $sql ?></code>
    
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Duración</th>
                    <th>Clasificación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($rows as $row): ?>
                <tr>
                    <td><?= $row['title'] ?></td>
                    <td><?= $row['category'] ?></td>
                    <td><?= $row['price'] ?></td>
                    <td><?= $row['length'] ?></td>
                    <td><?= $row['rating'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>  

        <div class="btn-group">
            <?php if ($prevLink): ?>
            <a class="btn btn-outline-primary" href="<?= $prevLink ?>">&#8592; Anteriores</a>
            <?php endif; ?>
            <?php if ($nextLink): ?>
            <a class="btn btn-outline-primary" href="<?= $nextLink ?>">Siguientes &#8594;</a>
            <?php endif; ?>
        </div>
    </div>  
</body>
</html>