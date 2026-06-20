import urllib.request
import urllib.parse
from http.cookiejar import CookieJar

cj = CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# Login first!
login_data = urllib.parse.urlencode({'email': 'admin@southmart.id', 'password': 'password'}).encode('utf-8')
opener.open('http://localhost:8000/quick-login/admin')

html = opener.open('http://localhost:8000/admin/produk').read().decode('utf-8')
print("deleteProduct function present?", "deleteProduct" in html)
print("onclick present?", "onclick=\"deleteProduct" in html)
