# EUS Room Booking System Changelog

20/03/2019: Andrei Ungur
- Created Github repository for room booking system
- Previous changelog entry contains code, please do not add code in the changelog it should be already visible in your PR. I did it for this particular entry only because this code was not previously on Github at all. :)


20/03/2019: Andrei Ungur
- Modified "max bookings" to work with Google OAuth. Previously, modifying the "Maximum number per day" setting (manually through the database, as the UI gives an error) limited bookings per day per AREA, not per USER.
- This is because the OAuth addition removes data entered in the `created_by` column of a new booking. I fixed this by querying bookings per day using e-mail as well, as it is unique for a user.
- Even when limiting bookings per day per user, the default didn't support having two one-hour bookings in an area where the limit is not one booking per day, but two hours overall.
- I modified the SQL query from this:
```
SELECT COUNT(*)
FROM $tbl_entry E, $tbl_room R
WHERE E.start_time < $interval_end
AND E.end_time > $interval_start
AND E.create_by='" .sql_escape($booking['create_by']) . "'
AND E.room_id = R.id
AND R.disabled=0
```
to this:
```
SELECT SUM(end_time - start_time)
FROM $tbl_entry E, $tbl_room R
WHERE E.start_time < $interval_end
AND E.end_time > $interval_start
AND E.create_by='" . sql_escape($booking['create_by']) . "'
AND E.room_id=R.id
AND E.Email='" . sql_escape($booking['Email']) . "'
AND R.disabled=0
```


01/01/2016: Lou Bernardi
- Add Google OAuth.
- This affected many files at once, and has not been thoroughly documented. Some comments have been left through the code as a breadcrumb trail for this massive update.
