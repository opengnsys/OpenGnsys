import requests
import unittest

class TestPostRestoreBasicImageMethods(unittest.TestCase):

    def setUp(self):
        self.url = 'http://localhost:8888/image/restore/basic'
        self.headers = {'Authorization' : '07b3bfe728954619b58f0107ad73acc1'}
        self.json = { 'clients' : [ '192.168.2.1', '192.168.2.2' ],
                      'disk': '1',
                      'partition': '1',
                      'id': '9',
                      'name': 'test',
                      'repository': '192.168.56.10',
                      'profile': '17',
                      'type': 'UNICAST',
                      'sync_params':{'path': '',
                                     'method': '1',
                                     'sync': '1',
                                     'diff': '0',
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
