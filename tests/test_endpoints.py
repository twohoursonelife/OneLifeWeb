import os
import re
import urllib.request
import urllib.parse
import unittest

class Test(unittest.TestCase):
    # Get base URL from environment variable, defaulting to http://localhost
    BASE_URL = os.environ.get("TEST_BASE_URL", "http://localhost").rstrip("/")

    def make_request(self, path, params=None):
        """Helper to make an HTTP request and return the status and body."""
        url = self.BASE_URL + path
        if params:
            url += "?" + urllib.parse.urlencode(params)
        
        req = urllib.request.Request(url, headers={'User-Agent': 'TestSuite/1.0'})
        try:
            with urllib.request.urlopen(req, timeout=5) as response:
                body = response.read().decode('utf-8', errors='replace')
                return response.status, body
        except urllib.error.HTTPError as e:
            body = e.read().decode('utf-8', errors='replace')
            return e.code, body
        except Exception as e:
            self.fail(f"Request to {url} failed: {e}")

    def assert_no_php_errors(self, body, url_info):
        """Analyse the response body to ensure no PHP errors are present."""
        php_error_patterns = [
            r"<b>Warning</b>",
            r"<b>Notice</b>",
            r"<b>Fatal error</b>",
            r"<b>Deprecated</b>",
            r"<b>Parse error</b>",
            r"Exception:",
            r"Error:",
            r"mysqli_connect",
            r"Access denied for user",
            r"Connection refused",
            r"Connection timed out"
        ]
        for pattern in php_error_patterns:
            if re.search(pattern, body, re.IGNORECASE):
                snippet = body[:500] + "..." if len(body) > 500 else body
                self.fail(f"PHP warning or error detected in response from {url_info}:\n{snippet}")

    def test_home(self):
        """Test if the main server index is reachable."""
        status, body = self.make_request("/")
        self.assertEqual(status, 200)
        self.assert_no_php_errors(body, "/")

    def test_setups(self):
        """Verify setup endpoints function correctly."""
        setups = {
            "ticketServer": "ts_setup",
            "lineageServer": "ls_setup",
            "reviewServer": "rs_setup",
            "photoServer": "ps_setup",
        }
        for server, action in setups.items():
            with self.subTest(server=server):
                path = f"/{server}/server.php"
                status, body = self.make_request(path, {"action": action})
                self.assertEqual(status, 200)
                self.assert_no_php_errors(body, f"{path}?action={action}")
                self.assertIn("table", body.lower())

    def test_frontend_pages(self):
        """Verify frontend page rendering and errors."""
        pages = [
            ("lineageServer", "front_page", "Two Hours One Life"),
            ("photoServer", "front_page", "Recent Photographs:"),
        ]
        for server, action, expected_text in pages:
            with self.subTest(server=server, action=action):
                path = f"/{server}/server.php"
                status, body = self.make_request(path, {"action": action})
                self.assertEqual(status, 200)
                self.assert_no_php_errors(body, f"{path}?action={action}")
                self.assertIn(expected_text, body)

    def test_reflector(self):
        """Verify reflector endpoints behave correctly."""
        # Reflect action
        status, body = self.make_request("/reflector/server.php", {"action": "reflect", "email": "test@example.com"})
        self.assertEqual(status, 200)
        self.assert_no_php_errors(body, "/reflector/server.php?action=reflect")
        lines = body.strip().split("\n")
        self.assertTrue(len(lines) >= 5)
        self.assertEqual(lines[-1].strip(), "OK")

        # Report action
        status, body = self.make_request("/reflector/server.php", {"action": "report"})
        self.assertEqual(status, 200)
        self.assert_no_php_errors(body, "/reflector/server.php?action=report")
        self.assertIn("Total :::", body)

    def test_ticket_actions(self):
        """Verify ticket server endpoints with dummy parameters."""
        actions = {
            "check_ticket": ("login_key", "DUMMY-KEY-123", "INVALID"),
            "get_ticket_email": ("login_key", "DUMMY-KEY-123", "INVALID"),
            "get_login_key": ("email", "doesnotexist@example.com", "DENIED"),
            "check_ticket_hash": ("email", "doesnotexist@example.com", "INVALID"),
        }
        for action, (param_name, param_val, expected_out) in actions.items():
            with self.subTest(action=action):
                status, body = self.make_request("/ticketServer/server.php", {"action": action, param_name: param_val})
                self.assertEqual(status, 200)
                self.assert_no_php_errors(body, f"/ticketServer/server.php?action={action}")
                self.assertEqual(body.strip(), expected_out)

    def test_diff_bundle_update(self):
        """Verify diff bundle update checking and retrieval."""
        # Check update availability
        status, body = self.make_request("/diffBundleServer/server.php", {
            "action": "is_update_available",
            "platform": "Linux",
            "old_version": "99999"
        })
        self.assertEqual(status, 200)
        self.assert_no_php_errors(body, "/diffBundleServer/server.php?action=is_update_available")
        self.assertEqual(body.strip(), "0")

        # Check update download endpoint
        status, body = self.make_request("/diffBundleServer/server.php", {
            "action": "get_update",
            "platform": "Linux",
            "old_version": "99999"
        })
        self.assertEqual(status, 200)
        self.assert_no_php_errors(body, "/diffBundleServer/server.php?action=get_update")
        self.assertEqual(body.strip(), "DENIED")

    def test_lineage_endpoints(self):
        """Verify lineage endpoints."""
        status, body = self.make_request("/lineageServer/server.php", {"action": "log_life"})
        self.assertEqual(status, 200)
        self.assert_no_php_errors(body, "/lineageServer/server.php?action=log_life")
        self.assertEqual(body.strip(), "DENIED")

    def test_review_endpoints(self):
        """Verify review endpoints."""
        # Stats endpoint
        status, body = self.make_request("/reviewServer/server.php", {"action": "get_stats"})
        self.assertEqual(status, 200)
        self.assert_no_php_errors(body, "/reviewServer/server.php?action=get_stats")
        self.assertEqual(body.strip(), "DENIED")

        # Log game endpoint
        status, body = self.make_request("/reviewServer/server.php", {"action": "log_game"})
        self.assertEqual(status, 200)
        self.assert_no_php_errors(body, "/reviewServer/server.php?action=log_game")
        self.assertEqual(body.strip(), "DENIED")

if __name__ == '__main__':
    unittest.main()
