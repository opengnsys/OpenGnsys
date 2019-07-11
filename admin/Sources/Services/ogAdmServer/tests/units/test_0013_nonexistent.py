import requests
import unittest

class TestPostNonexistentMethods(unittest.TestCase):

    def setUp(self):
        self.url = 'http://localhost:8888/nonexistent'
        self.headers = {'Authorization' : '07b3bfe728954619b58f0107ad73acc1'}
        self.wrong_headers = {'Authorization' :
                'WrongWrongWrongWrongWrongWrongWr'}
        self.json = { 'clients' : [ '192.168.2.1', '192.168.2.2' ] }

    def test_post(self):
        returned = requests.post(self.url, headers=self.headers, json=self.json)
        self.assertEqual(returned.status_code, 404)

    def test_get(self):
        returned = requests.get(self.url, headers=self.headers)
        self.assertEqual(returned.status_code, 404)

    def test_post_unauthenticated(self):
        returned = requests.post(self.url, headers=self.wrong_headers)
        self.assertEqual(returned.status_code, 401)

    def test_post_without_json(self):
        returned = requests.post(self.url, headers=self.headers)
        self.assertEqual(returned.status_code, 404)

if __name__ == '__main__':
    unittest.main()
