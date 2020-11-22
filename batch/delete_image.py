# 不要課題画像削除バッチ

import json
import mysql.connector
import os
import shutil

HOST = 'mysql740.db.sakura.ne.jp'
PORT = '3306'
USER = 'ojitopo'
PASSWORD = 'ojitopo3267'
DB = 'ojitopo_admin'

IMAGE_FILE_DIR = '/home/ojitopo/www/dev/public/assets/image'
PROBLEM_FILE_DIR = IMAGE_FILE_DIR + '/problem'
TEMP_DIR = IMAGE_FILE_DIR + '/temp'

def get_image_filenames():
    try:
        conn = mysql.connector.connect(
            host = HOST,
            port = PORT,
            user = USER,
            password = PASSWORD,
            database = DB,
        )

        cur = conn.cursor()

        sql = '''
SELECT images FROM problem_stock
UNION (SELECT images FROM monthly_stock)
        '''

        cur.execute(sql)

        file_names = []

        for row in cur.fetchall():
            file_names.extend(json.loads(row[0])['img'])
        return (file_names)
    finally:
        conn.close

def remove_unused_file(file_names: list):
    os.mkdir(TEMP_DIR)

    for name in file_names:
        image_dir = PROBLEM_FILE_DIR + '/' + name
        if not os.path.exists(image_dir): continue
        shutil.move(image_dir, TEMP_DIR)
    
    shutil.rmtree(PROBLEM_FILE_DIR)
    os.rename(TEMP_DIR, PROBLEM_FILE_DIR)

image_files = get_image_filenames()
remove_unused_file(image_files)
