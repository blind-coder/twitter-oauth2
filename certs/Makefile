gateway.pem:
	@echo "Please enter a passphrase for the private when prompted"
	openssl req -newkey rsa:2048 -x509 -keyout gateway.pem -out gateway.crt -days 36500

gateway.nopass.pem: gateway.pem
	@echo "Please enter the passphrase again to remove it from the private key (required)"
	openssl rsa -in gateway.pem -out gateway.nopass.pem

gateway.pub: gateway.nopass.pem
	openssl x509 -in gateway.crt -pubkey -noout > gateway.pub

all: gateway.pub
	@echo "Done. Please use https://mkjwk.org/ to create a JWKS and put it in this directory as jwks.json"
