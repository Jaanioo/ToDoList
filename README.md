# ToDoList
> ToDoList API application created with PHP, Symfony, MySQL and Docker

## Table of Contents
* [General Info](#general-information)
* [Technologies Used](#technologies-used)
* [Features](#features)
* [Usage](#usage)
* [Project Status](#project-status)
* [Acknowledgements](#acknowledgements)
* [Contact](#contact)


## General Information
- This application is API for ToDoList.
- You can create new user and login with valid credentials.
- You can add, edit or delete tasks for your account.
- I made it for learning Symfony and show my skills for recrutation process.

## Technologies Used
- PHP - version 8.2
- Symfony - version 6
- Docker


## Features
List the ready features here:
- Sending API requests
- Sending emails after create user or change password
- Creating JWT when user log in


## Usage
You should clone repository to your local machine or use my VPS 

**To use it locally:**

`cd desktop
git clone https://github.com/Jaanioo/ToDoList.git Jaanioo
cd jaanioo
symfony serve -d`

Go to your browser and type 'http://127.0.0.1:8000'

**To use it via VPS:** 

Go to your browser and open that site: 

http://3.137.169.190:8080

**All available paths:**
1. api/v1/user
   1. /all - display all registred users
   2. /register - create new user 
   3. /login - log in 
   4. /change - change password for user
2. api/v1/task
   1. /user - display all tasks for logged user
   2. /user/{bool} - display tasks according to completed status ({bool} = 1/0)
   3. /all - display all tasks for every user
   4. /{id} - display task with given id
   5. /new - create new task
   6. /{id}/edit - edit task with given id ({id} = int)
   7. /{id}/delete - delete task with given id ({id} = int)

## Project Status
Project is: _in progress_ 


## Acknowledgements
- Symfony documentation
- PHP documentation
- Many youtube videos


## Contact
Created by [@Jan Paleń](https://www.linkedin.com/in/jan-palen/) - feel free to contact me!
