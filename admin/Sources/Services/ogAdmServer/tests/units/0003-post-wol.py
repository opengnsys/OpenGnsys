import requests
import unittest

class TestPostWolMethods(unittest.TestCase):

    def setUp(self):
        self.url = 'http://localhost:8888/wol'
        self.json = { 'type' : 'broadcast', 'clients' : { 'addr' : '192.168.2.1',
            'mac' : '00AABBCCDD01' } }

    def test_post(self):
        returned = requests.post(self.url, json=self.json)
        self.assertEqual(returned.status_code, 200)

    def test_get(self):
        returned = requests.post(self.url)
        self.assertEqual(returned.status_code, 404)

if __name__ == '__main__':
    unittest.main()
