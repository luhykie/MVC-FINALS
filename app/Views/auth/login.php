<?php

use Core\Session;
use Core\View\Engine as View;
?>
<form method="post" action="/login">
    <!-- CSRF token protects this POST form. -->
    <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">

    <div>
        <!-- Default demo credentials are prefilled for easier testing. -->
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="admin@usjr.edu.ph" required>
    </div>

    <div>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" value="password" required>
    </div>

    <button type="submit">Login</button>
</form>
