gateway.pem:
	@echo "Please enter a passphrase for the private when prompted"
	openssl req -newkey rsa:2048 -x509 -keyout gateway.pem -out gateway.crt -days 36500

gateway.nopass.pem: gateway.pem
	@echo "Please enter the passphrase again to remove it from the private key (required)"
	openssl rsa -in gateway.pem -out gateway.nopass.pem

gateway.pub: gateway.nopass.pem
	openssl rsa -in gateway.nopass.pem -pubout > gateway.pub

all: gateway.pub
	@echo "Done. Please use https://russelldavies.github.io/jwk-creator/ to create a JWKS from gateway.pub (enter key id ("kid") 0) and put it in this directory as jwks.json"
