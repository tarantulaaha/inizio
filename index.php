<?php

use voku\db\DB;

require_once 'vendor/autoload.php';
include "Ares.php";
session_start();
if (!isset($_SESSION['orderby'])) {
    $_SESSION['orderby'] = 'date';
}
if (!isset($_SESSION['smer'])) {
    $_SESSION['smer'] = 'DESC';
}
$prevPage = 0;
$page = 1;
$nextPage = 0;
$rowPerPage = 3;

if (isset($_GET['p'])) {
    if (is_numeric($_GET['p'])) {
        $page = $_GET['p'];
    }
}
?>
<html>
<head>
    <title>Vyhledávání podle IČO</title>
</head>
<body>
<h2>Vyhledávání podle IČO</h2>
<div>
    <form>
        <input type="text" name="ico" placeholder="Zadejte IČO">
        <button type="submit">Hledat</button>
    </form>
</div>
<?php
$db = DB::getInstance('localhost', 'root', 'Zaq1Xsw2', 'inizio');
try {
    if (isset($_GET['ico'])) {
        try {
            $result = (new Ares)->search($_GET['ico']);
            $newUserId = $db->insert('search_results', $result);
        } catch (Exception $e) {
            echo "<div style='color:red'>{$e->getMessage()}</div>";
        }
    }

    $result = $db->query("SELECT COUNT(*) FROM search_results");
    $rowCount = $result->fetch()->{'COUNT(*)'};
    $pagesCount = ceil($rowCount / $rowPerPage);
    if ($page > $pagesCount) {
        $page = $pagesCount;
    }
    if ($page < 1) {
        $page = 1;
    }
    $prevPage = $page - 1;
    $nextPage = $page + 1;
    ?>
    <div>
        <?php
        echo "<div>Celkem záznamů: {$rowCount}</div>";
        $startRecord = ($page - 1) * $rowPerPage;
        if (isset($_GET['s'])) {
            $_SESSION['smer'] = $_GET['s'];
        }
        if (isset($_GET['orderby'])) {
            if ($_SESSION['orderby'] != $_GET['orderby']) {
                $_SESSION['orderby'] = $_GET['orderby'];
                $_SESSION['smer'] = 'DESC';
            }

        }
        $result = $db->query("SELECT * FROM search_results ORDER BY `{$_SESSION['orderby']}` {$_SESSION['smer']} LIMIT {$startRecord}, {$rowPerPage}");
        $articles = $result->fetchAll();


        ?>
        <table>
            <thead>
            <tr>
                <td>
                    <a href="?orderby=date&s=<?php echo ($_SESSION['orderby'] == 'date') ? ($_SESSION['smer'] == 'DESC') ? 'ASC' : 'DESC' : 'DESC'; ?>&p=<?php echo $page; ?>">Datum
                        vyhledání</a></td>
                <td class="colCompany">company</td>
                <td>
                    <a href="?orderby=ic&s=<?php echo ($_SESSION['orderby'] == 'ic') ? ($_SESSION['smer'] == 'DESC') ? 'ASC' : 'DESC' : 'DESC'; ?>&p=<?php echo $page; ?>">IČO</a>
                </td>
                <td>Město</td>
                <td>Země</td>
                <td>PSČ</td>
                <td>Ulice</td>
                <td>Záznam</td>
            </tr>
            </thead>
            <?php foreach ($articles as $article) {
                ?>
                <tr>
                    <td><?php echo date('d.m.Y H:i:s', $article->date); ?></td>
                    <td><?php echo $article->company ?></td>
                    <td><?php echo $article->ic ?></td>
                    <td><?php echo $article->city ?></td>
                    <td><?php echo $article->country ?></td>
                    <td><?php echo $article->zip ?></td>
                    <td><?php echo $article->street ?></td>
                    <td><?php echo $article->record ?></td>

                </tr>
                <?php
            }
            ?>
        </table>
        <div>
            <?php
            if ($page > 1) {
                echo "<a href='?p={$prevPage}' class='pageNav'><<</a>";
            }
            for ($i = 1; $i <= $pagesCount; $i++) {
                echo "<a href='?p={$i}' class='pageNav'>{$i}</a>";
            }

            if ($page < $pagesCount) {
                echo "<a href='?p={$nextPage}' class='pageNav'>>></a>";
            }
            ?>
        </div>
    </div>
    <?php
} catch (Exception $e) {
    echo "<div style='color: red'>{$e->getMessage()}</div>";

}
?>
<style>
    td {
        border: 1px solid;
        margin: 0px;
    }

    .pageNav {
        margin: 0px 5px;
    }

    .colCompany {
        width: 300px;
    }
</style>
</body>
</html>
