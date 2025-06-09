/*
processLogin.js
Javascript file that checks the login email enty to validate that it has the creighton domain name.
Made by Ryan King
November 19, 2020
*/

function validateEmail() {
  var input = document.getElementById("formEmail").value;
  var domain = input.substring(input.length-14, input.length);

    if (domain != "@creighton.edu") {
        document.getElementById("errorMsg").innerHTML = "Invalid email. "
                + "Please enter your Creighton email.";
        return false;
     } else {
        return true;
     }
}
