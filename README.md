easytrack
=========

Fast use:
-----------
Edit the exemple/tracker.php, with your database's host/db/user/password
Can change table's name, filter etc...

On top of your page/index/bootstrap/kernel... (on very top of your app)
-----------

    include_once 'navigationTracker.php';
    $nav = new navigationTracker();

Before end of body
-----------

    <?php echo $nav->getScript('<path to>/tracker.php'); ?>

That's all !
This will store in your database, all http requests, with all information gathered by your visitor's navigator


Now you probably want to show the data stored in your database, in the back-end of your app ?
Little more difficult, but you can use renderTracker.php to help you.
The best is to see an exemple of use, have a look at exemple/render.php, you can edit it for your app use as well.

Your can try this with :
http://www.ludo-portfolio.fr/creation/custom/6
