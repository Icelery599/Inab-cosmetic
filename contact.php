<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2>Contact Us</h2>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Send us a Message</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>First Name</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Last Name</label>
                                <input type="text" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Subject</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Message</label>
                            <textarea class="form-control" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5>Contact Information</h5>
                </div>
                <div class="card-body">
                    <p><i class="fas fa-map-marker-alt"></i> <strong>Address:</strong><br>
                        123 Beauty Street<br>
                        Cosmetic City, CC 12345
                    </p>

                    <p><i class="fas fa-phone"></i> <strong>Phone:</strong><br>
                        (555) 123-4567
                    </p>

                    <p><i class="fas fa-envelope"></i> <strong>Email:</strong><br>
                        info@glamourbeauty.com
                    </p>

                    <p><i class="fas fa-clock"></i> <strong>Business Hours:</strong><br>
                        Mon-Fri: 9AM-8PM<br>
                        Sat: 9AM-6PM<br>
                        Sun: 10AM-4PM
                    </p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6>Follow Us</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="btn btn-outline-primary"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="btn btn-outline-info"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn btn-outline-danger"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="btn btn-outline-success"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>