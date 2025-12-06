<?php
return [
    '{{ Main_Home_Page_Title }}' => 'Home page',
    '{{ Week_Set_Page_Title }}' => 'Set week schedule',
    '{{ Day_Show_Page_Title }}' => 'Event',
    '{{ Day_Set_Page_Title }}' => 'Setting event',
    '{{ Users_List_Page_Title }}' => 'Users List',
    '{{ Settings_List_Page_Title }}' => 'System’s settings',
    '{{ Users_List_Title }}' => 'Users list',
    '{{ Settings_List_Title }}' => 'System’s settings',
    '{{ Chats_List_Title }}' => 'Chats with our Bot',

    '{{ HEADER_MENU_START_PAGE }}' => 'About Club',
    '{{ HEADER_MENU_NEWS }}' => 'News',
    '{{ HEADER_MENU_WEEKS }}' => 'Games Schedule',

    '{{ Account_Login_Form_Login_Input_Placeholder }}' => 'Login/e-mail',
    '{{ Account_Login_Form_Password_Input_Placeholder }}' => 'Password',
    '{{ Account_Login_Form_Forget_link }}' => 'Forget password?',
    '{{ Account_Login_Form_Register_Link }}' => 'Register',

    '{{ Account_Profile_Form_Title }}' => 'Agent’s profile «<b>%s</b>»',
    '{{ Account_Profile_Form_User_Avatar }}' => 'Users avatar of %s',

    '{{ Account_Register_Form_Title }}' => 'Registration form',

    '{{ Account_Password_Reset_Title }}' => 'Reset password',
    '{{ Account_Forget_Form_Title }}' => 'Recovery password',
    '{{ Account_Forget_Check_Succes }}' => "Your password reset request has been processed.\nYour password reset link:\n%s",

    '{{ Settings_Add_Title }}' => 'Add setting',
    '{{ Settings_Edit_Title }}' => 'Edit setting',

    '{{ Week_Set_Block_Title }}' => 'Set week schedule',

    'Mafia' => 'Mafia',
    // 'Poker' => 'Poker',
    'Board' => 'Board Games',
    'NLH' => 'NLH',
    'Etc' => 'Etc',

    '{{ Tg_Mafia }}' => 'Mafia 🎭',
    // '{{ Tg_Poker }}' => 'Poker ♦️',
    '{{ Tg_Board }}' => 'Board Games 🎲',
    '{{ Tg_NLH }}' => 'NLH 🃏',
    '{{ Tg_Etc }}' => 'Etc ✨',

    '{{ Send_To_All }}' => 'All chats',
    '{{ Send_To_Groups }}' => 'Groups',
    '{{ Send_To_Personal }}' => 'Personal chats',
    '{{ Send_To_Main }}' => 'Main chat',
    '{{ Send_To_Tech }}' => 'Technical chat',

    '{{ Day_Date_Not_Set }}' => '&lt;No data&gt; New week',

    '{{ News_Show_List_Page_Title }}' => 'News',
    '{{ News_Block_Read_More }}' => 'Read more',
    '{{ News_Show_Item_Page_Title }}' => 'News',
    '{{ News_Edit_Page_Title }}' => 'Edit news',
    '{{ News_Change_Logo }}' => 'Change main image',
    '{{ News_Edit_Block_Title }}' => 'Edit news',
    '{{ News_Add_Page_Title }}' => 'Create news',
    '{{ News_Add_Block_Title }}' => 'Create news',

    '{{ Page_Add_Page_Title }}' => 'Create new Page',
    '{{ Page_Add_Block_Title }}' => 'Create new Page',

    '{{ Tg_Unknown_Requester }}' => "Sorry! I don't recognize you in makeup🤷‍♂️\nLet's get acquainted!😏\nI'm a digital bot for booking. Your faithful friend and assistant in the matter of signing up for the games of our club🤗\nNow, please tell me your nickname in the game so that I can remember you!\nWrite: /nick <b>Your nickname</b> (<b>Cyrillic</b>!)\nExample:\n/nick Телеграм Бот",
    '{{ Tg_Command_Without_Arguments }}' => "This <b>Command</b> can't be executed without any arguments!",

    '{{ Tg_Command_Name_Already_Set }}' => "I already remember you as <b>%s</b>!\n\nChanges only through administrators",
    '{{ Tg_Command_Games_Not_Set }}' => "There are no games scheduled for the near future!\nPlease contact us later.\n",
    '{{ Tg_Command_Name_You_Have_One }}' => 'This nickname is already yours! Nice to meet you!😊',
    '{{ Tg_Command_Successfully_Canceled }}' => "Day settings successfully recalled!\n\n*You can cancel ''recall'' by admin's registering for that day;)",
    '{{ Tg_Gameday_Not_Set }}' => "There are no games scheduled for this day!",
    '{{ Tg_Command_Set_Day_Not_Found }}' => "There are no games scheduled for this day.",

    '{{ Tg_Command_Requester_Already_Booked }}' => "You are already signed up for this day's games!",
    '{{ Tg_Command_Requester_Not_Booked }}' => "You weren't signed up for this day's games!",

    '{{ Tg_User_With_Telegramid }}' => "User with registered Telegram.",
    '{{ Tg_Command_User_Already_Booked }}' => "This user is already signed up for today's games!",
    '{{ Tg_Command_User_Not_Booked }}' => "This user has not been signed up for this day's games!",
    '{{ Tg_Command_Name_Already_Set_By_Other }}' => "The nickname <b>%s</b> is already registered by another member of the group!\n\nIf this is you, then contact the Administrators to make changes!",
    '{{ Tg_Command_Name_Save_Success }}' => "So... we remember you under the nickname <b>%s</b>. Right?\n\nIf you make a mistake, don't worry, tell the administrator about it and he will quickly fix it😏",
    '{{ Tg_Command_New_User_Already_Set }}' => "The player under the nickname <b>%s</b> is already registered in the system!",
    '{{ Tg_Command_New_User_Save_Success }}' => "The player under the nickname <b>%s</b> is successfully registered in the system!\n\n*Hint him about the desirability of registering this alias, or do it manually in the admin panel😏",
    '{{ Tg_Command_Promo_Saved }}' => 'Promo successfully saved!',
    '{{ Tg_Command_Help }}' => "<i>My Instruction:</i>.\n
<b>Commands</b>:\n
+ (week day) <i>// Booking for the scheduled games of the current week, examples:</i>
    +вс
    + на сегодня, на 19:30 (отсижу 1-2 игры, под ?)
- (week day) <i>// Unsubscribe from games on a specific day that you previously signed up for, examples:</i>
    -вс
    - завтра

<u>/week</u> <i>// Schedule of upcoming games</i>
<u>/today</u> <i>// Booking information for today</i>
<u>/day (week day)</u> <i>// Booking information for a specific day. Without specifying the day - for today</i>
<u>/nick Your nickname</u> (Cyrillic) <i>// Register your nickname</i>
<u>/?</u> или <u>/help</u> <i>// This message</i>",
    '{{ Tg_Command_Help_Admin }}' => "\n\n<b><u>Admin's commands</u></b>:
<u>/reg</u> <i>// Booking/unbooking players for a specific day. Examples:</i>
    /reg +вс, nickname, 18:00, (with ?)
    /reg -вс, nickname

<u>/set</u> <i>// Set data for a specific day. Example:</i>
    /set вс, mafia, 18:00, (Good luck, have fun!)

<u>/newuser Player’s nickname</u> (in Cyrillic) <i>// Register a new nickname in the system.</i>
<u>/recall (week day)</u> <i>// Recall day settings for a specific day. Restored by a new registration from the admin. Without specifying the day - for today</i>
<u>/users</u> <i>// Users list, registered in system.</i>
<u>/promo</u> <i>// Fix notification that is added at the bottom of the /week command.</i> The text before the first line break is the title, before the second one is the subtitle, everything below is the text of the alert. Example:
    /promo Title
Subtitle
Text, or: here could be your <b>Advertising</b><i>:)</i>",
];
