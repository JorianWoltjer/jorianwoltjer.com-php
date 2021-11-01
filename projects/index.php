<?php require_once("../include/header.php"); ?>

    <h1 class="my-4"><code>Projects</code></h1>
    <div class="row">
        <?php
        $response = sql_query("SELECT title, text, category, img, href FROM projects ORDER BY id");

        if ($response) {
            while ($row = $response->fetch_assoc()) {
                ?>
                <div class="col-lg-4 col-sm-6 mb-4">
                    <div class="card h-100">
                        <a href="<?= $row["href"] ?>" <?= ($row["category"] == "Utility") ? "" : "target='_blank'" ?>>
                            <img class="card-img-top" src="/img/projects/<?= $row["img"] ?>"></a>
                        <div class="card-body">
                            <span class="tag tag-<?= $row["category"] ?>"><?= ucfirst($row["category"]) ?></span>
                            <h4 class="card-title">
                                <a href="<?= $row["href"] ?>" <?= ($row["category"] == "Utility") ? "" : "target='_blank'" ?>>
                                    <code><?= $row["title"] ?></code></a>
                            </h4>
                            <p class="card-text"><?= $row["text"] ?></p>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>

<?php require_once("../include/footer.php"); ?>