<?php
include '../config/connection.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all active lotteries
$lotteriesResult = mysqli_query($conn, "SELECT * FROM lotteries WHERE is_active=1 ORDER BY created_at DESC");

// Fetch last 3 lotteries for carousel
$carouselResult = mysqli_query($conn, "SELECT * FROM lotteries WHERE is_active=1 ORDER BY created_at DESC LIMIT 3");
?>



<!-- Carousel -->
<div class="container-fluid p-0 mt-3">
    <div id="lotteryCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $active = "active";
            while ($carouselItem = mysqli_fetch_assoc($carouselResult)):
                $photo = $carouselItem['photo'] ? '../' . htmlspecialchars($carouselItem['photo']) : 'https://via.placeholder.com/1200x400?text=No+Image';
                ?>
                <div class="carousel-item <?= $active ?>">
                    <img src="<?= $photo ?>" class="d-block w-100 rounded" style="height: 400px; object-fit: cover;" alt="<?= htmlspecialchars($carouselItem['title']) ?>">
                    <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-4">
                        <h5><?= htmlspecialchars($carouselItem['title']) ?></h5>
                        <p><?= htmlspecialchars($carouselItem['description']) ?></p>
                        <div class="row text-center">
                            <div class="col-6">
                                <i class="bi bi-trophy-fill" style="font-size: 1.8rem;"></i>
                                <h4>£<?= number_format($carouselItem['winning_price'], 2) ?></h4>
                                <small>Winning Prize</small>
                            </div>
                            <div class="col-6">
                                <i class="bi bi-cash-coin" style="font-size: 1.8rem;"></i>
                                <h4>£<?= number_format($carouselItem['total_raised'], 2) ?></h4>
                                <small>Raised</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                $active = "";
            endwhile;
            ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#lotteryCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#lotteryCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>

<!-- Tabs -->
<div class="container mt-4">
    <ul class="nav nav-tabs justify-content-center" id="categoryTabs" role="tablist">
        <?php
        $categories = ['All', 'Ending Soon', 'Instant Wins', 'Cars & Bikes', 'Cash', 'Tech & Luxury'];
        foreach ($categories as $index => $category) {
            $id = strtolower(str_replace([' ', '&'], ['-', 'and'], $category));
            $active = $index === 0 ? 'active' : '';
            echo "
            <li class='nav-item' role='presentation'>
                <button class='nav-link $active' id='{$id}-tab' data-bs-toggle='tab' data-category='{$id}' type='button' role='tab'>{$category}</button>
            </li>";
        }
        ?>
    </ul>
</div>

<!-- Cards Grid -->
<div class="container my-5">
    <div class="row row-cols-1 row-cols-md-4 g-4" id="lotteryGrid">
        <?php while ($lottery = mysqli_fetch_assoc($lotteriesResult)):
            $categoryClass = strtolower(str_replace([' ', '&'], ['-', 'and'], $lottery['category']));
            $photo = $lottery['photo'] ? '../' . htmlspecialchars($lottery['photo']) : 'https://via.placeholder.com/300x200?text=No+Image';
            $progress = $lottery['money_to_raise'] > 0 ? min(100, ($lottery['total_raised'] / $lottery['money_to_raise']) * 100) : 0;
        ?>
        <div class="col lottery-card" data-category="<?= $categoryClass ?>">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="position-relative">
                    <img src="<?= $photo ?>" class="card-img-top rounded-top-4" style="height: 180px; object-fit: cover;" alt="<?= htmlspecialchars($lottery['title']) ?>">
                    <?php
                    $expiry = new DateTime($lottery['expiry_date']);
                    $today = new DateTime();
                    $diff = $today->diff($expiry);
                    if ($expiry->format('Y-m-d') === $today->format('Y-m-d')) {
                        echo "<span class='badge bg-success position-absolute top-0 start-0 m-2 px-3 py-1 fw-semibold' style='font-size: 0.75rem;'>Draw Today 10pm</span>";
                    } elseif ($expiry > $today) {
                        echo "<span class='badge bg-warning text-dark position-absolute top-0 start-0 m-2 px-3 py-1 fw-semibold' style='font-size: 0.75rem;'>{$diff->days} day" . ($diff->days == 1 ? '' : 's') . " left</span>";
                    }
                    ?>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($lottery['title']) ?></h5>
                    <p class="small text-muted" style="min-height: 45px;"><?= htmlspecialchars($lottery['description']) ?></p>
                    <div class="mb-2">
                        <small><strong>Winning Price:</strong> £<?= number_format($lottery['winning_price'], 2) ?></small><br>
                        <small><strong>Price:</strong> £<?= number_format($lottery['price'], 2) ?></small>
                    </div>
                    <div class="progress mb-2" style="height: 20px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $progress ?>%;" aria-valuenow="<?= (int) $progress ?>" aria-valuemin="0" aria-valuemax="100"><?= round($progress, 1) ?>%</div>
                    </div>
                    <form method="post" action="cart.php" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="lottery_id" value="<?= $lottery['id'] ?>" />
                        <input type="hidden" name="price" value="<?= $lottery['price'] ?>" />
                        <input type="hidden" name="expiry_date" value="<?= $lottery['expiry_date'] ?>" />
                        <input type="number" min="1" name="quantity" value="1" class="form-control form-control-sm" style="width: 70px;" />
                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm flex-grow-1">Enter now</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
    document.querySelectorAll('#categoryTabs button').forEach(button => {
        button.addEventListener('click', () => {
            const category = button.dataset.category;
            document.querySelectorAll('.lottery-card').forEach(card => {
                const cardCategory = card.dataset.category;
                if (category === 'all' || category === cardCategory) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
