<?php
include '../config/connection.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all active lotteries ordered by created_at DESC
$allLotteriesResult = mysqli_query($conn, "SELECT * FROM lotteries WHERE is_active=1 ORDER BY created_at DESC");

// Fetch last 3 lotteries for carousel
$carouselResult = mysqli_query($conn, "SELECT * FROM lotteries WHERE is_active=1 ORDER BY created_at DESC LIMIT 3");
?>

<!-- Nav tabs -->
<nav class="container mt-4 mb-3">
    <ul class="nav nav-tabs justify-content-center" id="lotteryTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="ending-soon-tab" data-bs-toggle="tab" data-bs-target="#ending-soon" type="button" role="tab" aria-controls="ending-soon" aria-selected="true">Ending Soon</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="instant-wins-tab" data-bs-toggle="tab" data-bs-target="#instant-wins" type="button" role="tab" aria-controls="instant-wins" aria-selected="false">Instant Wins</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="cars-bikes-tab" data-bs-toggle="tab" data-bs-target="#cars-bikes" type="button" role="tab" aria-controls="cars-bikes" aria-selected="false">Cars & Bikes</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="cash-tab" data-bs-toggle="tab" data-bs-target="#cash" type="button" role="tab" aria-controls="cash" aria-selected="false">Cash</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tech-luxury-tab" data-bs-toggle="tab" data-bs-target="#tech-luxury" type="button" role="tab" aria-controls="tech-luxury" aria-selected="false">Tech & Luxury</button>
        </li>
    </ul>
</nav>

<!-- Carousel showing last 3 lotteries -->
<div class="container mb-5">
    <div id="lotteryCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php 
            $active = "active";
            while ($carouselItem = mysqli_fetch_assoc($carouselResult)): 
                $photo = $carouselItem['photo'] ? '../' . htmlspecialchars($carouselItem['photo']) : 'https://via.placeholder.com/1200x400?text=No+Image';
            ?>
            <div class="carousel-item <?= $active ?>">
                <img src="<?= $photo ?>" class="d-block w-100 rounded" style="height: 400px; object-fit: cover;" alt="<?= htmlspecialchars($carouselItem['title']) ?>">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
                    <h5><?= htmlspecialchars($carouselItem['title']) ?></h5>
                    <p><?= htmlspecialchars($carouselItem['description']) ?></p>
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

<!-- Lottery Cards Grid -->
<div class="container">
    <h2 class="mb-4 text-center fw-bold">Ending Soon</h2>
    <div class="row row-cols-1 row-cols-md-4 g-4">
        <?php while ($lottery = mysqli_fetch_assoc($allLotteriesResult)): 
            $photo = $lottery['photo'] ? '../' . htmlspecialchars($lottery['photo']) : 'https://via.placeholder.com/300x200?text=No+Image';
            $progress = 0;
            if ($lottery['money_to_raise'] > 0) {
                $progress = min(100, ($lottery['total_raised'] / $lottery['money_to_raise']) * 100);
            }
            $price = number_format($lottery['price'], 2);
            $winning_price = number_format($lottery['winning_price'], 2);
        ?>
        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="position-relative">
                    <img src="<?= $photo ?>" class="card-img-top rounded-top-4" style="height: 180px; object-fit: cover;" alt="<?= htmlspecialchars($lottery['title']) ?>">
                    <?php
                        $expiry_date = new DateTime($lottery['expiry_date']);
                        $today = new DateTime('today');
                        $interval = $today->diff($expiry_date);
                        if ($expiry_date->format('Y-m-d') === $today->format('Y-m-d')) {
                    ?>
                        <span class="badge bg-success position-absolute top-0 start-0 m-2 px-3 py-1 fw-semibold" style="font-size: 0.75rem;">Draw Today 10pm</span>
                    <?php
                        } elseif ($expiry_date > $today) {
                    ?>
                        <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2 px-3 py-1 fw-semibold" style="font-size: 0.75rem;">
                            <?= $interval->days ?> day<?= $interval->days == 1 ? '' : 's' ?> left
                        </span>
                    <?php
                        }
                    ?>
                </div>
                <div class="card-body">
                    <h5 class="card-title fw-semibold"><?= htmlspecialchars($lottery['title']) ?></h5>
                    <p class="card-text small text-muted" style="min-height: 45px;"><?= htmlspecialchars($lottery['description']) ?></p>
                    <div class="mb-2">
                        <small><strong>Winning Price:</strong> £<?= $winning_price ?></small><br>
                        <small><strong>Price:</strong> £<?= $price ?></small>
                    </div>
                    <div class="progress mb-2" style="height: 20px; border-radius: 10px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $progress ?>%;" aria-valuenow="<?= (int)$progress ?>" aria-valuemin="0" aria-valuemax="100"><?= round($progress, 1) ?>%</div>
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

<?php include '../includes/footer.php'; ?>
