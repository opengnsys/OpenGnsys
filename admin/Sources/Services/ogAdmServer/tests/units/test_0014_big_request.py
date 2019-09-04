import requests
import unittest

MAX_REQ_SIZE = 4096

class TestBigRequest(unittest.TestCase):

    def setUp(self):
        self.url = 'http://localhost:8888/clients'
        self.data = 'X' * MAX_REQ_SIZE

    def test_post(self):
        with self.assertRaises(requests.exceptions.ConnectionError) as context:
            requests.post(self.url, data=self.data)

        self.assertTrue('Connection reset by peer' in str(context.exception))

if __name__ == '__main__':
    unittest.main()
