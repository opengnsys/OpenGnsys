import requests
import unittest

class TestPostWrongHeaders(unittest.TestCase):

    def setUp(self):
        self.url = 'http://localhost:8888/clients'
        self.too_large_content_length_headers = {'Authorization' :
                '07b3bfe728954619b58f0107ad73acc1', 'Content-Length' :
                '999999999999999999999999999999999999999999999999999999999'}
        self.too_large_auth_headers = {'Authorization' :
                'TooLongoTooLongTooLongTooLongTooLongTooLongTooLongTooLong'
                'TooLongoTooLongTooLongTooLongTooLongTooLongTooLongTooLong'
                'TooLongoTooLongTooLongTooLongTooLongTooLongTooLongTooLong'}
        self.json = { 'clients' : [ '192.168.2.1', '192.168.2.2' ] }

    def test_post_too_large_content(self):
        with self.assertRaises(requests.exceptions.ConnectionError) as context:
            returned = requests.post(self.url,
                    headers=self.too_large_content_length_headers)

        self.assertTrue('Connection aborted' in str(context.exception))

    def test_post_too_large_auth(self):
        returned = requests.post(self.url, headers=self.too_large_auth_headers)
        self.assertEqual(returned.status_code, 401)

if __name__ == '__main__':
    unittest.main()
