  Gestion des Absences – Projet PHP

Ce projet permet de gérer les absences des étudiants dans une faculté.

Fonctionnalités

Ajouter / modifier / supprimer des étudiants

Ajouter / modifier / supprimer des modules

Enregistrer les absences

Afficher les listes et recherches

Connexion pour les utilisateurs

Installation

Cloner le projet :
if u have ssh key: git clone git@github.com:Zemoo8/gestion-absences-faculte.git
if u dont have ssh key: git clone https://github.com/Zemoo8/gestion-absences-faculte.git

Importer la base de données (fichier .sql) dans phpMyAdmin

Vérifier les paramètres de connexion dans config.php

Travail en équipe

La branche main contient le code stable

Chaque fonctionnalité sera faite dans une branche séparée
gestion-absences-faculte/
│
├─ README.md
├─ config.php
├─ index.php
│
├─ assets/
│   ├─ css/
│   │   └─ style.css
│   ├─ js/
│   │   └─ script.js
│   └─ images/
│
├─ controllers/
│   ├─ authController.php
│   ├─ studentController.php
│   ├─ moduleController.php
│   └─ attendanceController.php
│
├─ models/
│   ├─ Student.php
│   ├─ Module.php
│   └─ Attendance.php
│
├─ views/
│   ├─ auth/
│   │   └─ login.php
│   ├─ professor/
│   │   ├─ dashboard.php
│   │   └─ attendance.php
│   ├─ admin/
│   │   ├─ dashboard.php
│   │   ├─ manage_students.php
│   │   └─ manage_modules.php
│   └─ partials/
│       ├─ header.php
│       ├─ footer.php
│       └─ sidebar.php
│
├─ database/
│   └─ backup.sql
│
└─ .gitignore


Auteurs

Ahmed

Farouk
