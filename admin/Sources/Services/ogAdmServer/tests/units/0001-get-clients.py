import requests
import unittest

class TestGetClientsMethods(unittest.TestCase):

    def setUp(self):
        self.url = 'http://localhost:8888/clients'

    def test_get(self):
        returned = requests.get(self.url)
        self.assertEqual(returned.status_code, 200)

    def test_post(self):
        returned = requests.post(self.url)
        self.assertEqual(returned.status_code, 404)

if __name__ == '__main__':
    unittest.main()
