<?php require_once("../include/header.php"); ?>

<style>
    .wrap {
        width: 100vw;
        height: 250px;
        padding: 0;
        overflow: hidden;
    }
    .frame {
        width: 1280px;
        height: 720px;
        border: 0;
        -ms-transform: scale(0.4);
        -moz-transform: scale(0.4);
        -o-transform: scale(0.4);
        -webkit-transform: scale(0.4);
        transform: scale(0.4);
        -ms-transform-origin: 0 0;
        -moz-transform-origin: 0 0;
        -o-transform-origin: 0 0;
        -webkit-transform-origin: 0 0;
        transform-origin: 0 0;
    }
</style>

    <h1 class="my-4"><code>Projects</code></h1>

    <div class="row">
        <div class="col-lg-4 col-sm-6 mb-4">
            <div class="card h-100">
                <?php
                if (!isset($_GET['iframe']) || $_GET['iframe'] < 4) {  // Stop at depth 4
                    $depth = ($_GET["iframe"] ?? 0) + 1;
                    ?>
                    <div class="wrap">
                        <iframe src="/projects/?iframe=<?= $depth ?>" class="frame card-img-top"></iframe>
                    </div>
                <?php }
                if (isset($_GET['iframe'])) {  // Remove navbar if in iframe ?>
                    <style>
                        .navbar {
                            display: none;
                        }
                        #page-content {
                            margin-top: 0;
                        }
                    </style>
                <?php } ?>
                <div class="card-body">
                    <h4 class="card-title">
                        <a href="#">
                            <code>This website!</code></a>
                    </h4>
                    <p class="card-text">To learn PHP and have a portfolio in the process, I made this site. At first,
                        it was completely static, but later I added more and more functionality. Nowadays, I mostly use
                        it for my blog to post hacking-related articles. The whole site and its history are now
                        open-source and viewable on <a href="https://github.com/JorianWoltjer/jorianwoltjer.com" target="_blank">Github</a>.</p>
                </div>
            </div>
        </div>

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