// resources/js/realtime/chat.js
import { ajaxSelector } from "../ajax_select2";


document.addEventListener("DOMContentLoaded", () => {
  ajaxSelector("#assignedTo", "/searching-user", "Search User", "body");

});
