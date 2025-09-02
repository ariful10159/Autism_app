# ASD Detection App

This is a web-based Autism Spectrum Disorder (ASD) screening and support application. The app allows users to register, fill out a questionnaire, upload a face photo, and receive personalized suggestions based on their inputs. It is built using PHP, SQLite, HTML, CSS, and JavaScript.

## Features

- **User Registration & Login:** Secure authentication system for users.
- **Profile Management:** Users can update their profile, including age, gender, jaundice history, and family history of ASD.
- **ASD Screening Questionnaire:** 10-question form to assess ASD indicators.
- **Face Detection:** Users can upload a photo for additional screening.
- **Personalized Suggestions:** Based on questionnaire and photo, users receive tailored advice and resources.
- **Prediction Confidence Visualization:** Graphical display of screening results.
- **Mobile Responsive Design:** Optimized for mobile and desktop screens.

## Technologies Used

- **Backend:** PHP 7+, SQLite
- **Frontend:** HTML5, CSS3, JavaScript
- **Charting:** Chart.js (for result visualization)

## Folder Structure

```
├── App/
├── assets/
│   ├── autism_art.png
│   ├── script.js
│   └── styles.css
├── data/
│   └── app.sqlite
├── uploads/
│   └── profiles/
├── config.php
├── dashboard.php
├── detect.php
├── detect_asd.py
├── detection.php
├── dummy.png
├── index.php
├── login.php
├── logout.php
├── profile.php
├── questionnaire.php
├── signup.php
├── suggestions.php
```

## How to Run Locally

1. **Clone the Repository:**
   ```
   git clone https://github.com/ariful10159/Autism_app.git
   ```
2. **Setup Local Server:**
   - Use XAMPP, WAMP, or any PHP server.
   - Place the project folder inside your server's `htdocs` or `www` directory.
3. **Database:**
   - SQLite database (`data/app.sqlite`) is auto-created on first run.
4. **Access the App:**
   - Open your browser and go to `http://localhost/Updated_App_Final_final1/`

## Usage

- **Sign Up:** Create a new account.
- **Login:** Access your dashboard.
- **Profile:** Update personal details and upload a profile photo.
- **Questionnaire:** Answer ASD screening questions.
- **Face Detection:** Upload a face photo for further analysis.
- **Suggestions:** View personalized advice and prediction confidence graph.

## Customization

- **Questions:** Edit `questionnaire.php` to change or add questions.
- **Styling:** Modify `assets/styles.css` for custom look and feel.
- **Database:** Update `config.php` for schema changes.

## License

This project is for educational and research purposes. Not for clinical use.

## Author

Developed by ariful10159

---
For any issues or contributions, please open an issue or pull request on GitHub.
