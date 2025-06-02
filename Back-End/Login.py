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

@app.route('/signup', methods=['GET', 'POST'])
def signup():
    if request.method == 'POST':
        username = request.form.get('username')
        password = request.form.get('password')

        if not username or not password:
            return "Missing username or password", 400

        password_hash = hash_password(password)

        try:
            register_user(username, password_hash)
            return "Signup successful!"
        except Exception as e:
            return f"Error during signup: {e}", 500
    else:
        # If it's a GET request, just render the form
        return render_template('signup.html')

        
@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form.get('username')
        password = request.form.get('password')

        if not username or not password:
            return "Missing username or password", 400

        password_hash = hash_password(password)

        try:
            conn = mariadb.connect(
                user="root",
                password="password",
                host="127.0.0.1",
                port=3306,
                database="user_details"
            )
            cursor = conn.cursor()

            cursor.callproc("user_login", (username, password_hash))

            # Now fetch the result directly
            result = cursor.fetchall()
            if result:
                return f"Welcome, {result[0][1]}!"
            else:
                return "Invalid username or password", 401

        except mariadb.Error as e:
            return f"Database error: {e}", 500
        finally:
            cursor.close()
            conn.close()
    else:
        return render_template('login.html')






if __name__ == '__main__':
    app.run(debug=True)
