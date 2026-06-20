import urllib.request
import urllib.parse
from http.cookiejar import CookieJar
import re

cj = CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

login_data = urllib.parse.urlencode({'email': 'admin@southmart.id', 'password': 'password'}).encode('utf-8')
opener.open('http://localhost:8000/quick-login/admin')

resp = opener.open('http://localhost:8000/admin/produk')
html = resp.read().decode('utf-8')

# Find all product rows in the table
# e.g. <td class="font-monospace fw-semibold">8990000000018</td> ... <td><strong>Sari Roti Sobek Coklat Keju</strong></td>
matches = re.findall(r'<td class="font-monospace fw-semibold">([^<]+)</td>.*?<td><strong>([^<]+)</strong></td>', html, re.DOTALL)
print("Products currently on page 1:")
for m in matches:
    print(f"- {m[0]}: {m[1].strip()}")
