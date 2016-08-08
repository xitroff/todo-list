Live version available at https://www.todo.hitrov.com
Please notice that current application's version tested only on desktop with latest Google Chrome.
Functionality, not styling, was preferable, please mind it.
Telegram messenger Bot API integration code is not available at this repository (only by additional request).

To run locally,

1. Run sql.sql on your MySQL database.
2. Modify classes/DB.php with your database credentials.
3. Telegram Bot should be programmed to send notifications to users (see above).
cronEmailNotifications.php and cronTelegramlNotifications.php scripts should run periodically (every minute).
4. testApi.php outputs debug information about what API methods (for Telegram Bot API) return.