import requests

s = requests.Session()
# Login as admin
resp = s.get('http://localhost:8000/quick-login/admin')
print("Login status:", resp.status_code)

# Get index to find csrf token
resp = s.get('http://localhost:8000/admin/produk')
text = resp.text
csrf_token = text.split('name="_token" value="')[1].split('"')[0]
print("CSRF Token:", csrf_token)

# Delete product 4
resp = s.post('http://localhost:8000/admin/produk/4', data={
    '_token': csrf_token,
    '_method': 'DELETE'
}, allow_redirects=False)

print("Delete status:", resp.status_code)
print("Redirect to:", resp.headers.get('Location'))

# Check if there is an error in session
resp = s.get('http://localhost:8000/admin/produk')
if 'Gagal menghapus' in resp.text:
    print("ERROR MESSAGE FOUND IN HTML")
    # extract the error
    idx = resp.text.find('Gagal menghapus')
    print(resp.text[idx:idx+100])
elif 'berhasil dihapus' in resp.text:
    print("SUCCESS MESSAGE FOUND IN HTML")
else:
    print("NO MESSAGE FOUND")
