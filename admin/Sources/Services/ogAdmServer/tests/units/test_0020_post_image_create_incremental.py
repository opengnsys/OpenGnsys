import requests
import unittest

class TestPostCreateIncrementalImageMethods(unittest.TestCase):

    def setUp(self):
        self.url = 'http://localhost:8888/image/create/incremental'
        self.headers = {'Authorization' : '07b3bfe728954619b58f0107ad73acc1'}
        self.json = { 'clients' : [ '192.168.2.1', '192.168.2.2' ],
                      'disk': '1',
                      'partition': '1',
                      'id': '3',
                      'name': 'basica1',
                      'repository': '192.168.56.10',
                      'sync_params':{'sync': '1',
                                     'path': '',
                                     'diff': '0',
                                     'diff_id': '4',
                                     'diff_name': 'p2',
                                     'remove': '1',
                                     'compress': '0',
                                     'cleanup': '0',
                                     'cache': '0',
                                     'cleanup_cache': '0',
                                     'remove_dst': '0'} }

    def test_post(self):
        returned = requests.post(self.url, headers=self.headers, json=self.json)
        self.assertEqual(returned.status_code, 200)

    def test_no_payload(self):
        returned = requests.post(self.url, headers=self.headers, json=None)
        self.assertEqual(returned.status_code, 400)

    def test_malformed_payload(self):
        for parameter in self.json:
            malformed_payload = self.json.copy()
            malformed_payload.pop(parameter)
            returned = requests.post(self.url,
                                     headers=self.headers,
                                     json=malformed_payload)
            self.assertEqual(returned.status_code, 400)

    def test_get(self):
        returned = requests.get(self.url, headers=self.headers)
        self.assertEqual(returned.status_code, 405)

if __name__ == '__main__':
    unittest.main()
