<?php
include 'config/dbconnection.php';
include 'config/session.php';

$status = 1;
$query = $conn->prepare("SELECT * FROM election WHERE status = ?");
$query->bind_param('i', $status);
$exec = $query->execute();
$temp = $query->get_result();
$show_results = $temp->fetch_assoc();

if ($show_results) {
    $check_election = $show_results['id'];
    $voting_status = $show_results['can_vote'];
    $endtime = $show_results['endtime']; // Election end time

    // Check if user has already voted
    $user_id = $_SESSION['login_id'];
    $sql = "SELECT * FROM votes WHERE voter_id = '$user_id' AND election_id = '$check_election'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        echo "<script> location.href = 'index.php?page=vote_details' </script>";
        exit();
    }

    $stmt = $conn->prepare("
        SELECT c.*, cat.name AS category_name, e.title AS election_name, e.id AS election_id, e.endtime AS endtime, e.can_vote
        FROM candidates c
        JOIN categories cat ON c.category_id = cat.id
        JOIN election e ON c.election_id = e.id
        WHERE e.status = ? AND e.can_vote = ?
    ");
    $stmt->bind_param('ii', $status, $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Corrected voting time check
    if ($row && ($voting_status == 1) && (strtotime($endtime) > time())) {
        $election_title = $row['election_name'];
        $election_id = $row['election_id'];
        ?>

        <div class="pagetitle">
            <h1><?php echo $election_title; ?></h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Vote</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard row">
            <form id="vote-form" class="col-xxl-4 col-xl-12">

                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle me-1"></i> Time Remaining: <span class="text-muted" id="countdown"></span>
                    <button type="button" class="btn-close" aria-label="Close" onclick="$(this).parent().fadeOut()"></button>
                </div>

                <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">
                <?php
                $categories = [];
                foreach ($result as $value) {
                    if (!empty($value['fellow_candidate_name'])) {
                        $categories[$value['category_name']][] = $value;
                    }
                }

                foreach ($categories as $categoryName => $categoryCandidates) {
                    ?>
                    <div class="card info-card candidates-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $categoryName; ?> <span>| Choose one</span></h5>
                            <?php
                            foreach ($categoryCandidates as $candidate) {
                                ?>
                                <div class="d-flex align-items-center row pt-3 pb-2">
                                    <div class="col-4 d-flex flex-column align-items-center justify-content-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <img class="card-icon rounded-circle" src="assets/img/profile/candidate/<?php echo $candidate['candidate_photo']; ?>" alt="Candidate Photo">
                                        </div>
                                        <span class="d-flex small small-text pt-2 text-nowrap text-sm-start text-md-center fw-bold"><?php echo $candidate['name']; ?></span>
                                    </div>
                                    <div class="ps-3 col-4 d-flex flex-column align-items-center justify-content-center">
                                        <label class="vote-check-label card-icon rounded-circle">
                                            <input required type="radio" class="vote-radio" name="vote-<?php echo strtolower(str_replace(' ', '-', $categoryName)); ?>" data-id="<?php echo $candidate['id']; ?>" data-category="<?php echo $candidate['category_id']; ?>" data-election="<?php echo $election_id; ?>">
                                            <div class="checkmark"></div>
                                        </label>
                                        <span class="d-flex small small-text pt-2 text-nowrap text-sm-start text-md-center fw-bold">Vote</span>
                                    </div>
                                </div>
                                <?php
                                if (next($categoryCandidates)) {
                                    echo '<hr>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>

                <div class="card candidates-card">
                    <div class="card-body">
                        <div class=" d-flex justify-content-between pt-3">
                            <button type="reset" class="btn btn-secondary">Reset</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>

            </form>
        </section>

    <?php } else {
        if ($voting_status == 0 || strtotime($endtime) < time()) { ?>
            <div class="pagetitle">
                <h1>Smart Vote</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Vote</li>
                    </ol>
                </nav>
            </div>

            <section class="section error-404 d-flex flex-column align-items-center justify-content-center">
                <img src="assets/img/img-2.svg" class="img-fluid py-5" alt="No active election!" style="max-width: 320px">
                <h2>Sorry, Voting time has ended.</h2>
                <a class="btn" href="controllers/app.php?action=results">View Results</a>
            </section>

        <?php } else { ?>
            <div class="pagetitle">
                <h1>Online Voting System</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Vote</li>
                    </ol>
                </nav>
            </div>

            <section class="section error-404 d-flex flex-column align-items-center justify-content-center">
                <img src="assets/img/img-2.svg" class="img-fluid py-5" alt="No active election!" style="max-width: 320px">
                <h2>No candidates added, Try to come back later.</h2>
                <a class="btn" href="controllers/app.php?action=logout">Logout</a>
            </section>
        <?php }
    }
} else { ?>
    <div class="container">
        <div class="pagetitle">
            <h1>Online Voting System</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Vote</li>
                </ol>
            </nav>
        </div>

        <section class="section error-404 d-flex flex-column align-items-center justify-content-center">
            <img src="assets/img/img-2.svg" class="img-fluid py-5" alt="No active election!" style="max-width: 320px">
            <h2>No active election now, See you next time.</h2>
            <a class="btn" href="controllers/app.php?action=logout">Logout</a>
        </section>
    </div>
<?php } ?>
