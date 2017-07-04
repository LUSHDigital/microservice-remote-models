# Configuration

## GRPC DNS Name
By default the package will expect the remote model to be available at a DNS name in the following pattern:

```
[MODEL_ONE_PLURAL]-[MODEL_TWO_PLURAL]
```

An example would be https://shops-addresses:5001. If however you want to override this you can set an environment
variable with a name in the following pattern:

```
REMOTE_MODEL_[MODEL_ONE_PLURAL]_[MODEL_TWO_PLURAL]_DNS
```

## GRPC Port
As a default the package will always assume that the gRPC port on the remote model is 50051. If this is not the case in
your scenario then you can override this by setting the `REMOTE_MODEL_GRPC_PORT` environment variable.