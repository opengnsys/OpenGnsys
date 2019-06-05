import requests
import unittest

class TestPostSessionMethods(unittest.TestCase):

    def setUp(self):
        self.url = 'http://localhost:8888/shell/run'
        self.json = { 'clients' : [ '192.168.2.1', '192.168.2.2' ],
                'disk' : '0', 'partition' : '1'}

    def test_post(self):
        returned = requests.post(self.url, json=self.json)
        self.assertEqual(returned.status_code, 200)

    def test_get(self):
        returned = requests.post(self.url)
        self.assertEqual(returned.status_code, 404)

if __name__ == '__main__':
    unittest.main()
