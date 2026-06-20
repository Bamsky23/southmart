import urllib.request
import urllib.parse
from http.cookiejar import CookieJar
import re

cj = CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# 1. Login
login_data = urllib.parse.urlencode({'email': 'admin@southmart.id', 'password': 'password'}).encode('utf-8')
try:
    resp = opener.open('http://localhost:8000/quick-login/admin')
    print("Login OK")
except Exception as e:
    print("Login error", e)

# 2. Get CSRF token from /admin/produk
resp = opener.open('http://localhost:8000/admin/produk')
html = resp.read().decode('utf-8')
match = re.search(r'name="_token"\s+value="([^"]+)"', html)
if match:
    csrf_token = match.group(1)
    print("CSRF Token:", csrf_token)
else:
    print("CSRF Token not found!")

# 3. Submit DELETE request for product 4
data = urllib.parse.urlencode({
    '_token': csrf_token,
    '_method': 'DELETE'
}).encode('utf-8')

try:
    req = urllib.request.Request('http://localhost:8000/admin/produk/18', data=data)
    resp = opener.open(req)
    final_url = resp.geturl()
    print("Final URL after delete:", final_url)
    
    html = resp.read().decode('utf-8')
    if 'Gagal menghapus' in html:
        idx = html.find('Gagal menghapus')
        print("ERROR:", html[idx:idx+150])
    elif 'berhasil dihapus' in html:
        print("SUCCESS MESSAGE FOUND")
    else:
        print("NO FLASH MESSAGE FOUND")
except Exception as e:
    print("Delete error", e)
