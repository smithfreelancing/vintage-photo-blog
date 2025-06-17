<?php
/*
* File: /vintage-photo-blog/includes/footer.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file contains the footer for the site.
* It is included at the bottom of most pages.
*/
?>
    </main>
    <footer class="site-footer bg-white border-top py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">© <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-right">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item">
                            <a href="<?php echo SITE_URL; ?>/about.php">About</a>
                        </li>
                        <li class="list-inline-item">
                            <a href="<?php echo SITE_URL; ?>/contact.php">Contact</a>
                        </li>
                        <li class="list-inline-item">
                            <a href="<?php echo SITE_URL; ?>/privacy.php">Privacy Policy</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
