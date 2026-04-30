<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
.card-letra {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 60px;
    height: 60px;
    margin: 8px;
    background: #f0f0f0;
    border-radius: 12px;
    font-size: 2rem;
    font-weight: bold;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>
</head>
<body>

    <?php
    $nombremio = "JORGEPABLO";
    for ($i = 0; $i < strlen($nombremio); $i++) {
        echo '<div class="card-letra">' . $nombremio[$i] . '</div>';
    }
    ?>
    
    <div class="primer-titulo" style="color: red; font: fallback; width: 100%; justify-content: center; display: flex; align-items: center;">
        <p class="Primer" Styles="aling-self:center;">Primer <?php echo "Hola mundo"; ?></p>
    </div>
    
</body>
</html> 