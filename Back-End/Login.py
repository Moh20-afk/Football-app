from flask import Flask, render_template, request
import mariadb
import hashlib
import sys
import os

# Set the correct path to your HTML folder
template_path = os.path.abspath('../Front-End')

app = Flask(__name__, template_folder=template_path)

# Hashing function
def hash_password(password):
    return hashlib.sha256(password.encode()).hexdigest()

# Register function using mariadb.connect
def register_user(username, password_hash):
    try:
        conn = mariadb.connect(
            user="root",
            password="password",  # replace with your actual password
            host="127.0.0.1",
            port=3306,
            database="user_details"
        )
    except mariadb.Error as e:
        print(f"Error connecting to MariaDB Platform: {e}")
        sys.exit(1)

    cursor = conn.cursor()
    
    try:
        cursor.callproc("user_proc", (username, password_hash))
        conn.commit()
    except mariadb.Error as e:
        print(f"Database error during procedure call: {e}")
        raise
    finally:
        cursor.close()
        conn.close()

# Routes
@app.route('/')
def index():
    return render_template('index.html')

@app.route('/signup', methods=['POST'])
def signup():
    username = request.form['username']
    password = request.form['password']
    password_hash = hash_password(password)

    try:
        register_user(username, password_hash)
        return "Signup successful!"
    except Exception as e:
        return f"Error during signup: {e}"


if __name__ == '__main__':
    app.run(debug=True)
