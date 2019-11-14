import requests
import unittest

class TestPostSetupMethods(unittest.TestCase):

    def setUp(self):
        self.url = 'http://localhost:8888/setup'
        self.headers = {'Authorization' : '07b3bfe728954619b58f0107ad73acc1'}
        self.json = { 'clients' : [ '192.168.2.1', '192.168.2.2' ],
                      'disk' : '1',
                      'cache' : '1',
                      'cache_size' : '0',
                      'partition_setup': [{'partition': '1',
                                           'code': 'LINUX',
                                           'filesystem': 'EMPTY',
                                           'size': '498688',
                                           'format': '0'},
                                          {'partition': '2',
                                           'code': 'LINUX-SWAP',
                                           'filesystem': 'EMPTY',
                                           'size': '199987',
                                           'format': '0'},
                                          {'partition': '3',
                                           'code': 'LINUX',
                                           'filesystem': 'EMPTY',
                                           'size': '31053824',
                                           'format': '0'},
                                          {'partition': '4',
                                           'code': 'EMPTY',
                                           'filesystem': 'EMPTY',
                                           'size': '0',
                                           'format': '0'}] }

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
