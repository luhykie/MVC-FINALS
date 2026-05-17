<?php

use App\Core\Session;
use App\Core\View;
?>
<form method="post" action="/login">
    <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">

    <div>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="admin@usjr.edu.ph" required>
    </div>

    <div>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" value="password" required>
    </div>

    <button type="submit">Login</button>
</form>
