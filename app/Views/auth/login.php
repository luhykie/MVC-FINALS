<?php

use Core\Session;
use Core\View\Engine as View;

$email = Session::flash('old_email') ?? '';
?>
<form method="post" action="/login">
    <!-- CSRF token mo-protect ani nga POST form. -->
    <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">

    <div>
        <label for="email">Email</label>
        <input id="email" name="email" value="<?= View::e($email) ?>">
    </div>

    <div>
        <label for="password">Password</label>
        <input id="password" name="password" type="password">
    </div>

    <button type="submit">Login</button>
</form>
