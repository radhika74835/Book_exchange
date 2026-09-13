<?php

session_start();
require_once "db_connect.php";


/* =========================================================
   1. CHECK LOGIN
   ========================================================= */

if (!isset($_SESSION["student_id"])) {

    header("Location: login.php");
    exit;
}


$currentStudentId = (int)$_SESSION["student_id"];


/* =========================================================
   2. GET SELECTED STUDENT
   ========================================================= */

$otherStudentId = (int)($_GET["student_id"] ?? 0);


/* =========================================================
   3. IF A STUDENT IS SELECTED
      CHECK ACCEPTED EXCHANGE
   ========================================================= */

if ($otherStudentId > 0) {

    /*
       Check whether there is an accepted exchange
       between the logged-in student and selected student.
    */

    $stmt = mysqli_prepare(
        $conn,
        "
        SELECT er.id

        FROM exchange_request er

        JOIN books b
            ON b.id = er.desired_book_id

        WHERE er.status = 'accepted'

        AND (
            (
                er.student_id = ?
                AND b.student_id = ?
            )

            OR

            (
                er.student_id = ?
                AND b.student_id = ?
            )
        )

        LIMIT 1
        "
    );


    mysqli_stmt_bind_param(
        $stmt,
        "iiii",
        $currentStudentId,
        $otherStudentId,
        $otherStudentId,
        $currentStudentId
    );


    mysqli_stmt_execute($stmt);


    $acceptedResult =
        mysqli_stmt_get_result($stmt);


    $acceptedExchange =
        mysqli_fetch_assoc($acceptedResult);


    /*
       Stop messaging if there is no accepted exchange.
    */

    if (!$acceptedExchange) {

        exit(
            "Messaging is available only after an exchange request is accepted."
        );

    }


    /* =====================================================
       4. GET OTHER STUDENT
       ===================================================== */

    $stmt = mysqli_prepare(
        $conn,
        "
        SELECT id, name

        FROM students

        WHERE id = ?

        LIMIT 1
        "
    );


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $otherStudentId
    );


    mysqli_stmt_execute($stmt);


    $otherStudent =
        mysqli_fetch_assoc(
            mysqli_stmt_get_result($stmt)
        );


    if (!$otherStudent) {

        exit("Student not found.");

    }


    /* =====================================================
       5. GET MESSAGES
       ===================================================== */

    $stmt = mysqli_prepare(
        $conn,
        "
        SELECT

            m.id,
            m.sender_id,
            m.receiver_id,
            m.content,
            m.sent_at,

            sender.name AS sender_name

        FROM messages m

        JOIN students sender
            ON sender.id = m.sender_id

        WHERE

        (
            m.sender_id = ?
            AND m.receiver_id = ?
        )

        OR

        (
            m.sender_id = ?
            AND m.receiver_id = ?
        )

        ORDER BY m.sent_at ASC
        "
    );


    mysqli_stmt_bind_param(
        $stmt,
        "iiii",
        $currentStudentId,
        $otherStudentId,
        $otherStudentId,
        $currentStudentId
    );


    mysqli_stmt_execute($stmt);


    $messages =
        mysqli_stmt_get_result($stmt);

} else {

    /*
       No student selected.

       Show a list of students with whom
       an exchange has been accepted.
    */

    $otherStudent = null;
    $messages = null;


    $stmt = mysqli_prepare(
        $conn,
        "
        SELECT DISTINCT

            s.id,
            s.name

        FROM exchange_request er

        JOIN books b
            ON b.id = er.desired_book_id

        JOIN students s
            ON s.id =
                CASE

                    WHEN er.student_id = ?
                    THEN b.student_id

                    ELSE er.student_id

                END

        WHERE er.status = 'accepted'

        AND (
            er.student_id = ?
            OR b.student_id = ?
        )

        AND s.id <> ?

        ORDER BY s.name ASC
        "
    );


    mysqli_stmt_bind_param(
        $stmt,
        "iiii",
        $currentStudentId,
        $currentStudentId,
        $currentStudentId,
        $currentStudentId
    );


    mysqli_stmt_execute($stmt);


    $conversationUsers =
        mysqli_stmt_get_result($stmt);
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Messages</title>


<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
>

</head>


<body class="bg-light">


<div
class="container py-5"
style="max-width:800px;"
>


<!-- =====================================================
     PAGE HEADER
     ===================================================== -->

<div class="d-flex justify-content-between align-items-center mb-4">

<h2>
💬 Messages
</h2>


<a
href="index.php"
class="btn btn-secondary"
>

Home

</a>

</div>



<?php if ($otherStudent): ?>


<!-- =====================================================
     CONVERSATION
     ===================================================== -->

<div class="card shadow-sm mb-4">


<div class="card-header">

<h5 class="mb-0">

Conversation with

<?php

echo htmlspecialchars(
    $otherStudent["name"]
);

?>

</h5>

</div>


<div class="card-body">


<?php if (mysqli_num_rows($messages) === 0): ?>


<div class="alert alert-info">

No messages yet.

Start the conversation below.

</div>


<?php else: ?>


<?php while (
    $message =
        mysqli_fetch_assoc($messages)
): ?>


<?php

$isMine =
    ((int)$message["sender_id"] === $currentStudentId);

?>


<div
class="mb-3
<?php echo $isMine ? 'text-end' : 'text-start'; ?>"
>


<div
class="d-inline-block p-3 rounded bg-white shadow-sm"
style="max-width:75%;"
>


<strong>

<?php

echo htmlspecialchars(
    $message["sender_name"]
);

?>

</strong>


<p class="mb-1 mt-1">

<?php

echo nl2br(
    htmlspecialchars(
        $message["content"]
    )
);

?>

</p>


<small class="text-muted">

<?php

echo date(
    "d-m-Y h:i A",
    strtotime($message["sent_at"])
);

?>

</small>


</div>

</div>


<?php endwhile; ?>


<?php endif; ?>


</div>

</div>



<!-- =====================================================
     SEND MESSAGE
     ===================================================== -->

<div class="card shadow-sm">


<div class="card-body">


<h5>
Send Message
</h5>


<form
method="post"
action="send_message.php"
>


<input
type="hidden"
name="receiver_id"
value="<?php
echo (int)$otherStudentId;
?>"
>


<textarea
name="content"
class="form-control mb-3"
rows="4"
required
placeholder="Type your message..."
></textarea>


<button
type="submit"
class="btn btn-primary"
>

📤 Send Message

</button>


<a
href="messages.php"
class="btn btn-secondary"
>

Back

</a>


</form>


</div>

</div>



<?php else: ?>


<!-- =====================================================
     NO STUDENT SELECTED
     SHOW ACCEPTED CONVERSATIONS
     ===================================================== -->


<div class="card shadow-sm">


<div class="card-body">


<h5 class="mb-3">

Available Conversations

</h5>


<?php if (
    mysqli_num_rows($conversationUsers) === 0
): ?>


<div class="alert alert-info">

Please open a conversation from an
accepted exchange request.

</div>


<?php else: ?>


<p class="text-muted">

You can message students after an
exchange request has been accepted.

</p>


<div class="list-group">


<?php while (
    $user =
        mysqli_fetch_assoc($conversationUsers)
): ?>


<a
href="messages.php?student_id=<?php
echo (int)$user["id"];
?>"
class="list-group-item list-group-item-action"
>


💬

<?php

echo htmlspecialchars(
    $user["name"]
);

?>


<span class="float-end">

Open →

</span>


</a>


<?php endwhile; ?>


</div>


<?php endif; ?>


</div>

</div>


<?php endif; ?>


</div>


</body>

</html>