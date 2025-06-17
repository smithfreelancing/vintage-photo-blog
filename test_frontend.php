<?php
/*
* File: /vintage-photo-blog/test_frontend.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file tests the front-end design implementation.
* It displays various UI components to verify styling.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = "Front-end Test";

// Include header
include 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-5">Front-end Design Test</h1>
    
    <section class="mb-5">
        <h2>Typography</h2>
        <div class="row">
            <div class="col-md-6">
                <h1>Heading 1</h1>
                <h2>Heading 2</h2>
                <h3>Heading 3</h3>
                <h4>Heading 4</h4>
                <h5>Heading 5</h5>
                <h6>Heading 6</h6>
            </div>
            <div class="col-md-6">
                <p>This is a paragraph of text. The design is minimalist and monochrome, focusing on typography and whitespace. The fonts are inspired by Le Labo's website, with a clean and elegant aesthetic.</p>
                <p><strong>This is bold text</strong> and <em>this is italic text</em>.</p>
                <p><a href="#">This is a link</a> that should have a hover effect.</p>
                <blockquote class="blockquote">
                    <p>This is a blockquote that demonstrates the styling of quoted content.</p>
                    <footer class="blockquote-footer">Someone famous</footer>
                </blockquote>
            </div>
        </div>
    </section>
    
    <section class="mb-5">
        <h2>Buttons</h2>
        <div class="mb-3">
            <button class="btn btn-dark mr-2">Dark Button</button>
            <button class="btn btn-outline-dark mr-2">Outline Button</button>
            <button class="btn btn-light mr-2">Light Button</button>
            <button class="btn btn-link">Link Button</button>
        </div>
        <div>
            <button class="btn btn-sm btn-dark mr-2">Small Button</button>
            <button class="btn btn-dark mr-2">Regular Button</button>
            <button class="btn btn-lg btn-dark">Large Button</button>
        </div>
    </section>
    
    <section class="mb-5">
        <h2>Forms</h2>
        <div class="row">
            <div class="col-md-6">
                <form>
                    <div class="form-group">
                        <label for="exampleInput1">Text Input</label>
                        <input type="text" class="form-control" id="exampleInput1" placeholder="Enter text">
                    </div>
                    <div class="form-group">
                        <label for="exampleInput2">Email Input</label>
                        <input type="email" class="form-control" id="exampleInput2" placeholder="Enter email">
                    </div>
                    <div class="form-group">
                        <label for="exampleSelect">Select</label>
                        <select class="form-control" id="exampleSelect">
                            <option>Option 1</option>
                            <option>Option 2</option>
                            <option>Option 3</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exampleTextarea">Textarea</label>
                        <textarea class="form-control" id="exampleTextarea" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-dark">Submit</button>
                </form>
            </div>
        </div>
    </section>
    
    <section class="mb-5">
        <h2>Cards</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-camera fa-3x text-secondary"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Card Title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                        <a href="#" class="btn btn-sm btn-outline-dark">Read More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-image fa-3x text-secondary"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Card Title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                        <a href="#" class="btn btn-sm btn-outline-dark">Read More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-film fa-3x text-secondary"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Card Title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                        <a href="#" class="btn btn-sm btn-outline-dark">Read More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="mb-5">
        <h2>Alerts</h2>
        <div class="alert alert-primary" role="alert">
            This is a primary alert—check it out!
        </div>
        <div class="alert alert-secondary" role="alert">
            This is a secondary alert—check it out!
        </div>
        <div class="alert alert-success" role="alert">
            This is a success alert—check it out!
        </div>
        <div class="alert alert-danger" role="alert">
            This is a danger alert—check it out!
        </div>
        <div class="alert alert-warning" role="alert">
            This is a warning alert—check it out!
        </div>
        <div class="alert alert-info" role="alert">
            This is a info alert—check it out!
        </div>
    </section>
    
    <section class="mb-5">
        <h2>Navigation</h2>
        <p>The main navigation is at the top of the page. Here's an example of pagination:</p>
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                </li>
                <li class="page-item"><a class="page-link" href="#">1</a></li>
                <li class="page-item active"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
    </section>
    
    <section>
        <h2>Icons</h2>
        <div class="row">
            <div class="col-md-6">
                <p>
                    <i class="fas fa-camera fa-2x mr-3"></i>
                    <i class="fas fa-image fa-2x mr-3"></i>
                    <i class="fas fa-film fa-2x mr-3"></i>
                    <i class="fas fa-user fa-2x mr-3"></i>
                    <i class="fas fa-heart fa-2x mr-3"></i>
                    <i class="fas fa-comment fa-2x"></i>
                </p>
                <p>
                    <i class="fab fa-instagram fa-2x mr-3"></i>
                    <i class="fab fa-facebook-f fa-2x mr-3"></i>
                    <i class="fab fa-twitter fa-2x mr-3"></i>
                    <i class="fab fa-pinterest fa-2x"></i>
                </p>
            </div>
        </div>
    </section>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
